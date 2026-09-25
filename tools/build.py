#!/usr/bin/env python3
"""
SOGETSURO ビルドツール（開発者向け・Python 3 のみで動きます）

  python3 tools/build.py

1. static/index.html の共通ヘッダー・フッター（<!-- sg:header --> / <!-- sg:footer --> の間）を
   ほかの static/*.html にコピーします（メニューを変えたら index.html だけ直してこれを実行）。
2. static/*.html から WordPress の「カスタムHTML」ブロックに貼る HTML（wordpress/pages/*.html）を作ります。
   ・画像のパス → /wp-content/uploads/sogetsuro/
   ・ページのリンク（reserve.html など）→ WordPress の URL（/reserve/ など）
   ・<!-- sg:wp ○○ --> … <!-- /sg:wp --> で囲んだ部分 → ○○（ショートコードなど）に置き換え
3. static/assets の CSS・JS を WordPress プラグイン（wordpress/sogetsuro-portal/assets）にコピーし、
   アップロード用の zip（wordpress/sogetsuro-portal.zip）を作ります。
"""
import re
import shutil
import sys
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
STATIC = ROOT / 'static'
WP = ROOT / 'wordpress'
PLUGIN = WP / 'sogetsuro-portal'
PAGES_OUT = WP / 'pages'

IMG_BASE = '/wp-content/uploads/sogetsuro/'

# 静的ページ → WordPress でのURL（固定ページのスラッグ）
LINKS = {
    'index.html': '/',
    'journal.html': '/journal/',
    'journal-article.html': '/journal/',
    'stay-omoya.html': '/stay-omoya/',
    'stay-hanare.html': '/stay-hanare/',
    'stay-kura.html': '/stay-kura/',
    'contact.html': '/contact/',
    'reserve.html': '/reserve/',
}

# WordPress の固定ページに貼る HTML を作るページ（記事一覧・記事ページはプラグインのテンプレートが表示）
WP_PAGES = [
    ('index.html', 'top.html', 'トップページ', '（「設定 → 表示設定」でホームページに指定）'),
    ('stay-omoya.html', 'stay-omoya.html', '宿の詳細（母屋）', 'stay-omoya'),
    ('stay-hanare.html', 'stay-hanare.html', '宿の詳細（離れ）', 'stay-hanare'),
    ('stay-kura.html', 'stay-kura.html', '宿の詳細（蔵）', 'stay-kura'),
    ('contact.html', 'contact.html', 'お問い合わせ', 'contact'),
    ('reserve.html', 'reserve.html', 'ご予約', 'reserve'),
]

BLOCK_RE = {
    name: re.compile(r'<!-- sg:%s\b.*?-->.*?<!-- /sg:%s\b.*?-->' % (name, name), re.S)
    for name in ('header', 'footer')
}
WP_REGION_RE = re.compile(r'^([ \t]*)<!-- sg:wp (.*?) -->\n.*?^[ \t]*<!-- /sg:wp -->\n', re.S | re.M)
LINK_RE = re.compile(r'(href|action)="(%s)([?#][^"]*)?"' % '|'.join(re.escape(k) for k in LINKS))


def sync_shared_blocks():
    """index.html の共通ヘッダー・フッターを他のページにコピー"""
    source = (STATIC / 'index.html').read_text(encoding='utf-8')
    blocks = {}
    for name, regex in BLOCK_RE.items():
        match = regex.search(source)
        if not match:
            sys.exit(f'index.html に sg:{name} のブロックが見つかりません')
        blocks[name] = match.group(0)

    for path in sorted(STATIC.glob('*.html')):
        if path.name == 'index.html':
            continue
        html = path.read_text(encoding='utf-8')
        updated = html
        for name, regex in BLOCK_RE.items():
            if not regex.search(updated):
                sys.exit(f'{path.name} に sg:{name} のブロックが見つかりません')
            updated = regex.sub(lambda _m, b=blocks[name]: b, updated, count=1)
        if updated != html:
            path.write_text(updated, encoding='utf-8')
            print(f'  共通ヘッダー・フッターを更新: static/{path.name}')


def to_wordpress(html):
    """<main> の中身を WordPress 用に変換"""
    start = html.index('<main class="sg-main">') + len('<main class="sg-main">')
    end = html.index('</main>')
    body = html[start:end].strip('\n')

    # 4文字分のインデントを外す
    lines = [line[4:] if line.startswith('    ') else line.lstrip(' ') for line in body.splitlines()]
    body = '\n'.join(lines).rstrip() + '\n'

    body = WP_REGION_RE.sub(lambda m: m.group(1) + m.group(2) + '\n', body)
    body = re.sub(r'(src|href)="assets/img/', lambda m: m.group(1) + '="' + IMG_BASE, body)
    body = LINK_RE.sub(lambda m: '%s="%s%s"' % (m.group(1), LINKS[m.group(2)], m.group(3) or ''), body)
    body = body.replace('href="/#', 'href="/#')

    leftovers = re.findall(r'(?:href|action|src)="(?!https?:|/|#|tel:|mailto:)[^"]*"', body)
    if leftovers:
        sys.exit('WordPress 用に変換できないリンクがあります: ' + ', '.join(sorted(set(leftovers))))
    return body


def build_wordpress_pages():
    PAGES_OUT.mkdir(parents=True, exist_ok=True)
    for source, target, title, slug in WP_PAGES:
        html = (STATIC / source).read_text(encoding='utf-8')
        slug_note = slug if slug.startswith('（') else f'「{slug}」'
        header = (
            '<!--\n'
            f'  SOGETSURO ─ {title}\n'
            '  ・固定ページの「カスタムHTML」ブロックに、このファイルの中身をすべて貼り付けてください。\n'
            '  ・ページのテンプレートは「SOGETSURO 共通レイアウト」を選んでください。\n'
            f'  ・スラッグ（URL）: {slug_note}\n'
            f'  ・画像は {IMG_BASE} にアップロードしてある前提です。\n'
            '-->\n'
        )
        (PAGES_OUT / target).write_text(header + to_wordpress(html), encoding='utf-8')
        print(f'  WordPress用HTML: wordpress/pages/{target}')


def build_plugin():
    if not (PLUGIN / 'sogetsuro-portal.php').exists():
        print('  （プラグイン本体がまだないため、アセットのコピーと zip 作成は省略）')
        return
    for rel in ('css/settings.css', 'css/style.css', 'js/main.js'):
        dest = PLUGIN / 'assets' / rel
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(STATIC / 'assets' / rel, dest)
    print('  プラグインに CSS・JS をコピー: wordpress/sogetsuro-portal/assets/')

    zip_path = WP / 'sogetsuro-portal.zip'
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as archive:
        for path in sorted(PLUGIN.rglob('*')):
            if path.is_file():
                archive.write(path, path.relative_to(WP).as_posix())
    print(f'  プラグインの zip を作成: wordpress/{zip_path.name}')


if __name__ == '__main__':
    print('1. 共通ヘッダー・フッターの同期')
    sync_shared_blocks()
    print('2. WordPress 用 HTML の作成')
    build_wordpress_pages()
    print('3. WordPress プラグイン')
    build_plugin()
    print('完了しました。')
