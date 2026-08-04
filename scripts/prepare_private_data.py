#!/usr/bin/env python3
from __future__ import annotations

import argparse
import re
import shutil
import sys
import zipfile
from pathlib import Path, PurePosixPath


def safe_extract(zip_path: Path, destination: Path) -> None:
    with zipfile.ZipFile(zip_path) as archive:
        for item in archive.infolist():
            path = PurePosixPath(item.filename)
            if path.is_absolute() or '..' in path.parts:
                raise ValueError(f'Caminho inseguro no ZIP: {item.filename}')
        archive.extractall(destination)


def count_files(directory: Path) -> int:
    return sum(1 for path in directory.rglob('*') if path.is_file())


def choose_attachments_directory(site_root: Path) -> Path:
    candidates = [
        path
        for path in site_root.rglob('anexos')
        if path.is_dir()
    ]

    if not candidates:
        raise FileNotFoundError('Não encontrei a pasta anexos no ZIP do site.')

    # A instalação publicada está dentro de public_html. O ZIP também contém
    # uma cópia secundária e desatualizada em ouvidoria/anexos.
    public_html_candidates = [
        path
        for path in candidates
        if path.parent.name.lower() == 'public_html'
    ]

    preferred = public_html_candidates or candidates

    # Em caso de mais de uma opção, seleciona a cópia mais completa.
    return max(preferred, key=count_files)


def main() -> int:
    parser = argparse.ArgumentParser(
        description='Prepara banco e anexos privados para execução local.'
    )
    parser.add_argument('site_zip', type=Path, help='ZIP original do site')
    parser.add_argument('database_zip', type=Path, help='ZIP original do banco SQL')
    args = parser.parse_args()

    project = Path(__file__).resolve().parents[1]
    work = project / '.prepare-temp'
    private = project / 'private'

    if work.exists():
        shutil.rmtree(work)

    (work / 'site').mkdir(parents=True)
    (work / 'db').mkdir(parents=True)

    safe_extract(args.site_zip.resolve(), work / 'site')
    safe_extract(args.database_zip.resolve(), work / 'db')

    source_attachments = choose_attachments_directory(work / 'site')

    target_attachments = private / 'anexos'
    target_attachments.mkdir(parents=True, exist_ok=True)

    for existing in target_attachments.iterdir():
        if existing.name != '.gitkeep':
            if existing.is_dir():
                shutil.rmtree(existing)
            else:
                existing.unlink()

    for source in source_attachments.rglob('*'):
        relative = source.relative_to(source_attachments)
        target = target_attachments / relative

        if source.is_dir():
            target.mkdir(parents=True, exist_ok=True)
        elif source.is_file():
            target.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(source, target)

    sql_files = [
        path
        for path in (work / 'db').rglob('*.sql')
        if path.is_file()
    ]

    if len(sql_files) != 1:
        raise RuntimeError(
            f'Esperava um arquivo SQL, encontrei {len(sql_files)}.'
        )

    sql = sql_files[0].read_text(encoding='utf-8', errors='replace')
    sql = re.sub(
        r'DEFINER=`[^`]+`@`[^`]+`',
        'DEFINER=CURRENT_USER',
        sql
    )

    target_db = private / 'database'
    target_db.mkdir(parents=True, exist_ok=True)

    for existing in target_db.glob('*.sql'):
        existing.unlink()

    (target_db / '001-ouvidoria.sql').write_text(
        sql,
        encoding='utf-8'
    )

    attachment_count = sum(
        1
        for path in target_attachments.rglob('*')
        if path.is_file() and path.name != '.gitkeep'
    )

    selected_source = source_attachments.relative_to(work / 'site')

    shutil.rmtree(work)

    print(f'Pasta de anexos selecionada: {selected_source}')
    print(f'Anexos copiados: {attachment_count}')
    print('Banco preparado: private/database/001-ouvidoria.sql')
    return 0


if __name__ == '__main__':
    try:
        raise SystemExit(main())
    except Exception as exc:
        print(f'Erro: {exc}', file=sys.stderr)
        raise SystemExit(1)