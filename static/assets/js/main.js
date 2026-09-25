/*!
 * SOGETSURO  main.js
 * ライブラリ不要（素の JavaScript のみ）。.sg-site があるページでだけ動きます。
 * ※ WordPress の本文（カスタムHTML）に直接貼っても壊れないよう、
 *   アンパサンド記号（論理演算子の AND など）は使わずに書いています。
 * -------------------------------------------------------------
 *  1. ローディング
 *  2. ヘッダー（メインビジュアルを過ぎたら色が変わる）／メインビジュアルの高さ
 *  3. メニュー（ハンバーガー）
 *  4. ページ内リンクのスムーススクロール
 *  5. メインビジュアルの文字アニメーション
 *  6. メインビジュアルのスライドショー
 *  7. スクロールで表示するアニメーション
 *  8. 画像のパララックス
 *  9. 流れるフォトギャラリー
 * 10. コピーライトの年
 * 11. 記事の目次（自動生成）
 * 12. 写真の拡大表示（ライトボックス）
 * 13. 予約（Beds24）：検索条件を予約画面に渡す
 * 14. SNSシェアボタン
 * 15. デザイン確認用フォーム（静的版のみ）
 */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  function each(list, fn) {
    Array.prototype.forEach.call(list, fn);
  }

  ready(function () {
    var root = document.querySelector('.sg-site');
    if (!root || root.getAttribute('data-sg-init')) return;
    root.setAttribute('data-sg-init', '1');

    var html = document.documentElement;
    var header = root.querySelector('.sg-header');
    var mainHero = root.querySelector('.sg-hero');                 // トップの全画面メインビジュアル
    var hero = root.querySelector('.sg-hero, .sg-page-hero');      // 下層ページの見出し画像も含む
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    html.classList.add('sg-js');

    var slider = initSlider();
    var drawer = initDrawer();
    initSplitText();
    initHeroHeight();
    initScrollState();
    initToc();
    initSmoothScroll();
    initReveal();
    initParallax();
    initMarquee();
    initLightbox();
    initBeds24();
    initShare();
    initDemoForm();
    initYear();
    initLoader();

    root.classList.add('is-ready');


    /* ---------------------------------------------------------
       1. ローディング（トップページのみ）
       --------------------------------------------------------- */
    function initLoader() {
      var loader = root.querySelector('.sg-loader');
      var finished = false;

      function finish() {
        if (finished) return;
        finished = true;
        root.classList.add('is-loaded');
        slider.start();
        try {
          sessionStorage.setItem('sg-visited', '1');
        } catch (e) { /* プライベートモード等では保存しない */ }
      }

      // 2回目以降の表示・ローディングなしのページはすぐに開始
      if (!loader || html.classList.contains('sg-visited') || reduceMotion) {
        setTimeout(finish, 80);
        return;
      }

      var MIN_TIME = 1500; // 最低表示時間（ミリ秒）
      var MAX_TIME = 4000; // 最大待ち時間（ミリ秒）
      var startTime = Date.now();
      var waits = [];
      var firstImage = root.querySelector('.sg-hero__slide img');

      if (firstImage) {
        if (!firstImage.complete) {
          waits.push(new Promise(function (resolve) {
            firstImage.addEventListener('load', resolve, { once: true });
            firstImage.addEventListener('error', resolve, { once: true });
          }));
        }
      }
      if (document.fonts) {
        waits.push(document.fonts.ready);
      }

      Promise.all(waits).then(function () {
        setTimeout(finish, Math.max(0, MIN_TIME - (Date.now() - startTime)));
      });
      setTimeout(finish, MAX_TIME);
    }


    /* ---------------------------------------------------------
       2. ヘッダー（メインビジュアルを過ぎたら .is-past-hero）
          見出し画像のないページ（記事など）は最初から白いヘッダー
       --------------------------------------------------------- */
    function initScrollState() {
      var ticking = false;

      function update() {
        ticking = false;
        var past = true;
        if (hero) {
          var headerBottom = header ? header.getBoundingClientRect().bottom : 0;
          past = hero.getBoundingClientRect().bottom <= headerBottom + 1;
        }
        root.classList.toggle('is-past-hero', past);
      }

      function onScroll() {
        if (!ticking) {
          ticking = true;
          requestAnimationFrame(update);
        }
      }

      window.addEventListener('scroll', onScroll, { passive: true });
      window.addEventListener('resize', onScroll);
      update();
    }


    /* メインビジュアルを「実際に見えている画面の高さ」に合わせる
       （スマホのアドレスバーで下が隠れないように。CSSの --sg-hero-h） */
    function initHeroHeight() {
      if (!mainHero) return;
      var lastWidth = 0;

      function update() {
        // スマホはスクロールでアドレスバーが伸び縮みするため、横幅が変わったときだけ更新
        if (window.innerWidth === lastWidth) {
          if (window.innerWidth < 1024) return;
        }
        lastWidth = window.innerWidth;
        root.style.setProperty('--sg-hero-h', window.innerHeight + 'px');
      }

      window.addEventListener('resize', update);
      update();
    }


    /* ---------------------------------------------------------
       3. メニュー（ハンバーガー）
       --------------------------------------------------------- */
    function initDrawer() {
      var button = root.querySelector('.sg-menu-btn');
      var panel = root.querySelector('.sg-drawer');
      var api = { close: function () {} };
      if (!button || !panel) return api;

      var label = button.querySelector('.sg-menu-btn__text');
      var behind = root.querySelectorAll('.sg-main, .sg-footer, .sg-pagetop, .sg-fixed-cta');
      var isOpen = false;

      function setOpen(open, restoreFocus) {
        isOpen = open;
        root.classList.toggle('is-menu-open', open);
        button.setAttribute('aria-expanded', String(open));
        button.setAttribute('aria-label', open ? 'メニューを閉じる' : 'メニューを開く');
        if (label) label.textContent = open ? 'Close' : 'Menu';

        if (open) {
          panel.removeAttribute('inert');
        } else {
          panel.setAttribute('inert', '');
        }
        each(behind, function (el) {
          if (open) {
            el.setAttribute('inert', '');
          } else {
            el.removeAttribute('inert');
          }
        });
        html.style.overflow = open ? 'hidden' : '';

        if (open) {
          var first = panel.querySelector('a');
          if (first) {
            setTimeout(function () { first.focus({ preventScroll: true }); }, 350);
          }
        } else if (restoreFocus) {
          button.focus({ preventScroll: true });
        }
      }

      button.addEventListener('click', function () {
        setOpen(!isOpen, true);
      });

      document.addEventListener('keydown', function (e) {
        if (!isOpen) return;
        if (e.key === 'Escape' || e.key === 'Esc') setOpen(false, true);
      });

      api.close = function () {
        if (isOpen) setOpen(false, false);
      };
      return api;
    }


    /* ---------------------------------------------------------
       4. ページ内リンクのスムーススクロール
          「#sg-access」「index.html#sg-access」「/#sg-access」など、
          いま開いているページ内へのリンクだけをなめらかに移動します
       --------------------------------------------------------- */
    function initSmoothScroll() {
      root.addEventListener('click', function (e) {
        var link = e.target.closest('a[href]');
        if (!link) return;

        // href="#"（リンク先未設定）はページ先頭へ飛ばないようにする
        if (link.getAttribute('href') === '#') {
          e.preventDefault();
          return;
        }
        if (link.target === '_blank') return;

        var url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) return;
        if (pagePath(url.pathname) !== pagePath(window.location.pathname)) return;
        if (url.search !== window.location.search) return;

        var target = url.hash ? document.getElementById(decodeURIComponent(url.hash.slice(1))) : root;
        if (!target) return;

        e.preventDefault();
        drawer.close();
        scrollToTarget(target);
      });
    }

    function pagePath(path) {
      return path.replace(/index\.html?$/, '');
    }

    function scrollToTarget(target) {
      var top = 0;
      if (target !== root) {
        top = target.getBoundingClientRect().top + window.pageYOffset;
        if (target !== hero) top -= headerOffset();
      }
      window.scrollTo({ top: Math.max(0, top), behavior: reduceMotion ? 'auto' : 'smooth' });

      // キーボード操作の人のために、移動先にフォーカスを移す
      if (target !== root) {
        if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
      }
    }

    function headerOffset() {
      if (!header) return 0;
      var rect = header.getBoundingClientRect();
      // PCではスクロール後にヘッダーが 72px に縮むため、小さい方に合わせる
      return rect.top + Math.min(rect.height, 72) + 16;
    }


    /* ---------------------------------------------------------
       5. メインビジュアルの文字アニメーション（1文字ずつ表示）
       --------------------------------------------------------- */
    function initSplitText() {
      var counters = [];

      each(root.querySelectorAll('[data-sg-split]'), function (el) {
        var text = el.textContent.trim();
        if (!text) return;

        var group = el.closest('[data-sg-split-group]') || el;
        var entry = null;
        for (var n = 0; n < counters.length; n++) {
          if (counters[n].group === group) entry = counters[n];
        }
        if (!entry) {
          entry = { group: group, count: 0 };
          counters.push(entry);
        }

        var srText = document.createElement('span');
        srText.className = 'sg-sr-only';
        srText.textContent = text;

        var visual = document.createElement('span');
        visual.setAttribute('aria-hidden', 'true');
        Array.from(text).forEach(function (ch) {
          var span = document.createElement('span');
          span.className = 'sg-split__char';
          span.textContent = ch;
          span.style.setProperty('--i', entry.count);
          entry.count += 1;
          visual.appendChild(span);
        });

        el.textContent = '';
        el.appendChild(srText);
        el.appendChild(visual);
      });
    }


    /* ---------------------------------------------------------
       6. メインビジュアルのスライドショー
          切り替え間隔は HTML の data-interval（ミリ秒）で変更できます
       --------------------------------------------------------- */
    function initSlider() {
      var api = { start: function () {} };
      var el = root.querySelector('[data-sg-slider]');
      if (!el) return api;

      var slides = Array.prototype.slice.call(el.querySelectorAll('.sg-hero__slide'));
      var current = el.querySelector('.sg-hero__current');
      var total = el.querySelector('.sg-hero__total');
      var bar = el.querySelector('.sg-hero__bar');
      var interval = parseInt(el.getAttribute('data-interval'), 10) || 6000;
      var index = 0;
      var timer = null;
      var prevTimer = null;
      var started = false;

      slides.forEach(function (slide, i) {
        if (slide.classList.contains('is-active')) index = i;
      });
      el.style.setProperty('--sg-hero-interval', interval + 'ms');
      if (total) total.textContent = pad(slides.length);
      if (current) current.textContent = pad(index + 1);

      function pad(num) {
        return (num < 10 ? '0' : '') + num;
      }

      function runBar() {
        if (!bar) return;
        bar.classList.remove('is-running');
        void bar.offsetWidth; // アニメーションを最初からやり直すため
        bar.classList.add('is-running');
      }

      function show(next) {
        if (next === index) return;
        var prev = slides[index];
        slides.forEach(function (slide) { slide.classList.remove('is-prev'); });
        prev.classList.remove('is-active');
        prev.classList.add('is-prev');
        slides[next].classList.add('is-active');
        index = next;
        if (current) current.textContent = pad(index + 1);

        clearTimeout(prevTimer);
        prevTimer = setTimeout(function () { prev.classList.remove('is-prev'); }, 2100);
        runBar();
      }

      function play() {
        stop();
        if (slides.length < 2) return;
        runBar();
        timer = setInterval(function () {
          show((index + 1) % slides.length);
        }, interval);
      }

      function stop() {
        clearInterval(timer);
        timer = null;
        if (bar) bar.classList.remove('is-running');
      }

      // タブが非表示の間は止める
      document.addEventListener('visibilitychange', function () {
        if (!started) return;
        if (document.hidden) {
          stop();
        } else {
          play();
        }
      });

      api.start = function () {
        if (started || reduceMotion) return;
        started = true;
        play();
      };
      return api;
    }


    /* ---------------------------------------------------------
       7. スクロールで表示するアニメーション（.sg-reveal）
          style="--d:.2s" のように書くと表示を遅らせられます
       --------------------------------------------------------- */
    function initReveal() {
      var items = root.querySelectorAll('.sg-reveal');
      if (!items.length) return;

      if (reduceMotion || !('IntersectionObserver' in window)) {
        each(items, function (item) { item.classList.add('is-inview'); });
        return;
      }

      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-inview');
            observer.unobserve(entry.target);
          }
        });
      }, { rootMargin: '0px 0px -10% 0px', threshold: 0 });

      each(items, function (item) { observer.observe(item); });
    }


    /* ---------------------------------------------------------
       8. 画像のパララックス（data-sg-parallax="0.1" で強さを指定）
       --------------------------------------------------------- */
    function initParallax() {
      if (reduceMotion) return;
      var items = Array.prototype.slice.call(root.querySelectorAll('[data-sg-parallax]'));
      if (!items.length) return;

      var SCALE = 1.16; // CSS の scale(1.16) と合わせる
      var ticking = false;

      function update() {
        ticking = false;
        var vh = window.innerHeight;
        items.forEach(function (el) {
          var rect = el.getBoundingClientRect();
          if (rect.bottom < -200 || rect.top > vh + 200) return;
          var speed = parseFloat(el.getAttribute('data-sg-parallax')) || 0.1;
          var limit = (rect.height * (SCALE - 1)) / 2;
          var y = (rect.top + rect.height / 2 - vh / 2) * -speed;
          y = Math.max(-limit, Math.min(limit, y));
          el.style.setProperty('--sg-py', y.toFixed(1) + 'px');
        });
      }

      function onScroll() {
        if (!ticking) {
          ticking = true;
          requestAnimationFrame(update);
        }
      }

      window.addEventListener('scroll', onScroll, { passive: true });
      window.addEventListener('resize', onScroll);
      update();
    }


    /* ---------------------------------------------------------
       9. 流れるフォトギャラリー
          写真を複製して、切れ目なくループさせます
       --------------------------------------------------------- */
    function initMarquee() {
      if (reduceMotion) return;

      each(root.querySelectorAll('[data-sg-marquee]'), function (marquee) {
        var track = marquee.querySelector('.sg-marquee__track');
        if (!track) return;

        Array.prototype.slice.call(track.children).forEach(function (item) {
          var clone = item.cloneNode(true);
          clone.setAttribute('aria-hidden', 'true');
          each(clone.querySelectorAll('img'), function (img) {
            img.setAttribute('alt', '');
          });
          track.appendChild(clone);
        });
        marquee.classList.add('is-running');

        // 近づいたら画像をまとめて読み込む（流れてきた写真が白く抜けないように）
        var images = track.querySelectorAll('img');
        var loadAll = function () {
          each(images, function (img) { img.loading = 'eager'; });
        };
        if ('IntersectionObserver' in window) {
          var observer = new IntersectionObserver(function (entries) {
            if (entries.some(function (entry) { return entry.isIntersecting; })) {
              loadAll();
              observer.disconnect();
            }
          }, { rootMargin: '600px 0px' });
          observer.observe(marquee);
        } else {
          loadAll();
        }
      });
    }


    /* ---------------------------------------------------------
       10. コピーライトの年を自動更新
       --------------------------------------------------------- */
    function initYear() {
      each(root.querySelectorAll('[data-sg-year]'), function (el) {
        el.textContent = String(new Date().getFullYear());
      });
    }


    /* ---------------------------------------------------------
       11. 記事の目次（大見出し h2・小見出し h3 から自動で作成）
          <div class="sg-toc" data-sg-toc hidden> を置くだけで動きます
       --------------------------------------------------------- */
    function initToc() {
      each(root.querySelectorAll('[data-sg-toc]'), function (toc) {
        var article = toc.closest('.sg-article') || root;
        var body = article.querySelector('.sg-article__body');
        var list = toc.querySelector('.sg-toc__list');
        if (!body || !list) return;

        var count = 0;
        each(body.querySelectorAll('h2, h3'), function (heading) {
          var text = heading.textContent.trim();
          if (!text) return;
          count += 1;
          if (!heading.id) heading.id = 'sg-toc-' + count;

          var item = document.createElement('li');
          item.className = 'sg-toc__item sg-toc__item--' + heading.tagName.toLowerCase();
          var link = document.createElement('a');
          link.href = '#' + heading.id;
          link.textContent = text;
          item.appendChild(link);
          list.appendChild(item);
        });

        // 見出しが2つ以上あるときだけ表示
        if (count >= 2) toc.removeAttribute('hidden');
      });
    }


    /* ---------------------------------------------------------
       12. 写真の拡大表示（data-sg-lightbox の中のリンク）
          <a href="大きい写真のURL" data-caption="説明"><img ...></a>
       --------------------------------------------------------- */
    function initLightbox() {
      var groups = root.querySelectorAll('[data-sg-lightbox]');
      if (!groups.length) return;

      function make(tag, className, attrs) {
        var node = document.createElement(tag);
        if (className) node.className = className;
        Object.keys(attrs || {}).forEach(function (key) { node.setAttribute(key, attrs[key]); });
        return node;
      }

      var box = make('div', 'sg-lightbox', { role: 'dialog', 'aria-modal': 'true', 'aria-label': '写真の拡大表示' });
      var closeBtn = make('button', 'sg-lightbox__close', { type: 'button', 'aria-label': '閉じる' });
      var prevBtn = make('button', 'sg-lightbox__prev', { type: 'button', 'aria-label': '前の写真' });
      var nextBtn = make('button', 'sg-lightbox__next', { type: 'button', 'aria-label': '次の写真' });
      var figure = make('figure', 'sg-lightbox__figure');
      var img = make('img', 'sg-lightbox__img', { alt: '' });
      var caption = make('figcaption', 'sg-lightbox__caption');
      var counter = make('p', 'sg-lightbox__count');
      figure.appendChild(img);
      figure.appendChild(caption);
      [closeBtn, prevBtn, nextBtn, figure, counter].forEach(function (node) { box.appendChild(node); });
      box.hidden = true;
      root.appendChild(box);

      var items = [];
      var index = 0;
      var lastFocus = null;
      var touchX = null;

      function show(i) {
        index = (i + items.length) % items.length;
        var link = items[index];
        var thumb = link.querySelector('img');
        img.src = link.getAttribute('href');
        img.alt = thumb ? thumb.alt : '';
        caption.textContent = link.getAttribute('data-caption') || img.alt;
        counter.textContent = (index + 1) + ' / ' + items.length;
        prevBtn.hidden = items.length < 2;
        nextBtn.hidden = items.length < 2;
      }

      function open(list, i) {
        items = list;
        lastFocus = document.activeElement;
        show(i);
        box.hidden = false;
        requestAnimationFrame(function () { box.classList.add('is-open'); });
        html.style.overflow = 'hidden';
        closeBtn.focus({ preventScroll: true });
      }

      function close() {
        box.classList.remove('is-open');
        html.style.overflow = '';
        setTimeout(function () { box.hidden = true; }, 400);
        if (lastFocus) lastFocus.focus({ preventScroll: true });
      }

      each(groups, function (group) {
        group.addEventListener('click', function (e) {
          var link = e.target.closest('a');
          if (!link) return;
          e.preventDefault();
          var list = Array.prototype.slice.call(group.querySelectorAll('a'));
          open(list, list.indexOf(link));
        });
      });

      closeBtn.addEventListener('click', close);
      prevBtn.addEventListener('click', function () { show(index - 1); });
      nextBtn.addEventListener('click', function () { show(index + 1); });
      box.addEventListener('click', function (e) {
        if (e.target === box) close();
      });

      document.addEventListener('keydown', function (e) {
        if (box.hidden) return;
        if (e.key === 'Escape' || e.key === 'Esc') close();
        if (e.key === 'ArrowLeft') show(index - 1);
        if (e.key === 'ArrowRight') show(index + 1);
      });

      // スマホ：左右にスワイプで写真を切り替え
      box.addEventListener('touchstart', function (e) {
        touchX = e.changedTouches[0].clientX;
      }, { passive: true });
      box.addEventListener('touchend', function (e) {
        if (touchX === null) return;
        var diff = e.changedTouches[0].clientX - touchX;
        touchX = null;
        if (Math.abs(diff) < 50) return;
        show(diff > 0 ? index - 1 : index + 1);
      });
    }


    /* ---------------------------------------------------------
       13. 予約（Beds24）
          空室検索フォーム（data-sg-search）で選んだ日付・人数を、
          予約画面の iframe（data-sg-beds24）に引き継ぎます
       --------------------------------------------------------- */
    function initBeds24() {
      var KEYS = ['checkin', 'checkout', 'numnight', 'numadult', 'numchild', 'roomid', 'lang'];
      var params = new URLSearchParams(window.location.search);

      each(root.querySelectorAll('iframe[data-sg-beds24]'), function (frame) {
        var src = frame.getAttribute('src');
        if (!src) return;
        var url = new URL(src, window.location.href);
        var changed = false;
        KEYS.forEach(function (key) {
          var value = params.get(key);
          if (value) {
            url.searchParams.set(key, value);
            changed = true;
          }
        });
        if (changed) frame.setAttribute('src', url.toString());
      });

      var today = new Date();
      var min = today.getFullYear() + '-' + pad2(today.getMonth() + 1) + '-' + pad2(today.getDate());

      each(root.querySelectorAll('form[data-sg-search]'), function (form) {
        each(form.querySelectorAll('input[type="date"]'), function (input) { input.min = min; });
        KEYS.forEach(function (key) {
          var field = form.elements[key];
          var value = params.get(key);
          if (field) {
            if (value) field.value = value;
          }
        });
      });

      function pad2(num) {
        return (num < 10 ? '0' : '') + num;
      }
    }


    /* ---------------------------------------------------------
       14. SNSシェアボタン（data-sg-share="x / facebook / line"）
          href="#" のときだけ、今のページのURLを入れます
       --------------------------------------------------------- */
    function initShare() {
      var pageUrl = window.location.href.split('#')[0];
      var bases = {
        x: ['https://twitter.com/intent/tweet', 'url', 'text'],
        facebook: ['https://www.facebook.com/sharer/sharer.php', 'u', ''],
        line: ['https://social-plugins.line.me/lineit/share', 'url', '']
      };

      each(root.querySelectorAll('[data-sg-share]'), function (link) {
        if (link.getAttribute('href') !== '#') return;
        var base = bases[link.getAttribute('data-sg-share')];
        if (!base) return;
        var url = new URL(base[0]);
        url.searchParams.set(base[1], pageUrl);
        if (base[2]) url.searchParams.set(base[2], document.title);
        link.href = url.toString();
      });
    }


    /* ---------------------------------------------------------
       15. デザイン確認用フォーム（静的版のみ・実際には送信しません）
          WordPress では Contact Form 7 のフォームに置き換わります
       --------------------------------------------------------- */
    function initDemoForm() {
      each(root.querySelectorAll('form[data-sg-demo-form]'), function (form) {
        form.addEventListener('submit', function (e) {
          e.preventDefault();
          var note = form.querySelector('.sg-form__demo-note');
          if (note) note.hidden = false;
        });
      });
    }
  });
})();
