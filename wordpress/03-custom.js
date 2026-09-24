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

  ready(function () {
    var root = document.querySelector('.sg-site');
    if (!root || root.getAttribute('data-sg-init')) return;
    root.setAttribute('data-sg-init', '1');

    var html = document.documentElement;
    var header = root.querySelector('.sg-header');
    var hero = root.querySelector('.sg-hero');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    html.classList.add('sg-js');

    var slider = initSlider();
    var drawer = initDrawer();
    initSplitText();
    initHeroHeight();
    initScrollState();
    initSmoothScroll();
    initReveal();
    initParallax();
    initMarquee();
    initYear();
    initLoader();

    root.classList.add('is-ready');


    /* ---------------------------------------------------------
       1. ローディング
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

      // 2回目以降の表示・ローディングなしの場合はすぐに開始
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
       --------------------------------------------------------- */
    function initScrollState() {
      var ticking = false;

      function update() {
        ticking = false;
        var past;
        if (hero) {
          var headerBottom = header ? header.getBoundingClientRect().bottom : 0;
          past = hero.getBoundingClientRect().bottom <= headerBottom + 1;
        } else {
          past = window.pageYOffset > 40;
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
      if (!hero) return;
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
        Array.prototype.forEach.call(behind, function (el) {
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
       --------------------------------------------------------- */
    function initSmoothScroll() {
      root.addEventListener('click', function (e) {
        var link = e.target.closest('a[href^="#"]');
        if (!link) return;

        var hash = link.getAttribute('href');
        // href="#"（リンク先未設定）はページ先頭へ飛ばないようにする
        if (hash === '#') {
          e.preventDefault();
          return;
        }

        var target = document.getElementById(decodeURIComponent(hash.slice(1)));
        if (!target) return;

        e.preventDefault();
        drawer.close();

        var top = target.getBoundingClientRect().top + window.pageYOffset;
        if (target !== hero) {
          top -= headerOffset();
        }
        window.scrollTo({ top: Math.max(0, top), behavior: reduceMotion ? 'auto' : 'smooth' });

        // キーボード操作の人のために、移動先にフォーカスを移す
        if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
      });
    }

    function headerOffset() {
      if (!header) return 0;
      var rect = header.getBoundingClientRect();
      // PCではスクロール後にヘッダーが 72px に縮むため、小さい方に合わせる
      return rect.top + Math.min(rect.height, 72);
    }


    /* ---------------------------------------------------------
       5. メインビジュアルの文字アニメーション（1文字ずつ表示）
       --------------------------------------------------------- */
    function initSplitText() {
      var counters = [];

      Array.prototype.forEach.call(root.querySelectorAll('[data-sg-split]'), function (el) {
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
        Array.prototype.forEach.call(items, function (item) { item.classList.add('is-inview'); });
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

      Array.prototype.forEach.call(items, function (item) { observer.observe(item); });
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

      Array.prototype.forEach.call(root.querySelectorAll('[data-sg-marquee]'), function (marquee) {
        var track = marquee.querySelector('.sg-marquee__track');
        if (!track) return;

        Array.prototype.slice.call(track.children).forEach(function (item) {
          var clone = item.cloneNode(true);
          clone.setAttribute('aria-hidden', 'true');
          Array.prototype.forEach.call(clone.querySelectorAll('img'), function (img) {
            img.setAttribute('alt', '');
          });
          track.appendChild(clone);
        });
        marquee.classList.add('is-running');

        // 近づいたら画像をまとめて読み込む（流れてきた写真が白く抜けないように）
        var images = track.querySelectorAll('img');
        var loadAll = function () {
          Array.prototype.forEach.call(images, function (img) { img.loading = 'eager'; });
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
      Array.prototype.forEach.call(root.querySelectorAll('[data-sg-year]'), function (el) {
        el.textContent = String(new Date().getFullYear());
      });
    }
  });
})();
