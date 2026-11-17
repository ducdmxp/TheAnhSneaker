<?php
/**
 * Mã đo lường quảng cáo. Điền ID vào các hằng số bên dưới, để trống = không tải.
 *
 * - Facebook Pixel: Trình quản lý sự kiện (Events Manager) > Nguồn dữ liệu.
 * - TikTok Pixel:   TikTok Ads Manager > Tài sản > Sự kiện > Web.
 * - Google Analytics 4: dạng "G-XXXXXXXXXX".
 * - Google Ads:     ID dạng "AW-XXXXXXXXX" + nhãn chuyển đổi (Conversion label) cho hành động
 *                   "Liên hệ" (bấm gọi / bấm Zalo).
 *
 * Sự kiện được gửi tự động khi khách bấm nút Zalo hoặc Gọi điện (xem script.js -> trackContact):
 *   Facebook: Contact | TikTok: Contact | GA4: generate_lead | Google Ads: conversion
 */
const FB_PIXEL_ID       = '1228037999368255';
const TIKTOK_PIXEL_ID   = '';
const GA4_ID            = '';
const GADS_ID           = '';
const GADS_CONTACT_LABEL = '';

function tracking_head(): void
{
    if (FB_PIXEL_ID !== '') { ?>
    <script>
      !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
      n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
      document,'script','https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '<?= FB_PIXEL_ID ?>');
      fbq('track', 'PageView');
    </script>
    <?php }

    if (TIKTOK_PIXEL_ID !== '') { ?>
    <script>
      !function (w, d, t) {
        w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=r+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
        ttq.load('<?= TIKTOK_PIXEL_ID ?>');
        ttq.page();
      }(window, document, 'ttq');
    </script>
    <?php }

    $gtagId = GA4_ID !== '' ? GA4_ID : GADS_ID;
    if ($gtagId !== '') { ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $gtagId ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      <?php if (GA4_ID !== ''): ?>gtag('config', '<?= GA4_ID ?>');<?php endif; ?>
      <?php if (GADS_ID !== ''): ?>gtag('config', '<?= GADS_ID ?>');<?php endif; ?>
    </script>
    <?php }

    $adsSendTo = (GADS_ID !== '' && GADS_CONTACT_LABEL !== '') ? GADS_ID . '/' . GADS_CONTACT_LABEL : '';
    ?>
    <script>window.TAS_TRACKING = { adsSendTo: <?= json_encode($adsSendTo) ?> };</script>
    <?php
}

function tracking_body(): void
{
    if (FB_PIXEL_ID !== '') { ?>
    <noscript><img height="1" width="1" style="display:none" alt=""
      src="https://www.facebook.com/tr?id=<?= FB_PIXEL_ID ?>&ev=PageView&noscript=1" /></noscript>
    <?php }
}
