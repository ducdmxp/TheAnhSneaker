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
 * Sự kiện gửi tự động (xem script.js):
 *   - Xem trang sản phẩm: Facebook ViewContent | TikTok ViewContent | GA4 view_item
 *   - Bấm Zalo / Gọi:     Facebook Contact | TikTok Contact | GA4 generate_lead | Google Ads conversion
 *
 * Facebook Conversions API: điền 'fb_capi_token' trong config.php (bí mật, không để ở đây).
 *
 * Mã xác minh tên miền (để trống nếu chưa có):
 * - FB_DOMAIN_VERIFICATION: Business Settings > Brand Safety > Domains > thêm tên miền > "Meta-tag",
 *   copy phần content="..." (chỉ chuỗi mã, không copy cả thẻ).
 * - GOOGLE_SITE_VERIFICATION: Google Search Console > thêm tài sản > "Thẻ HTML", copy phần content="...".
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/seo.php';

const FB_PIXEL_ID        = '1228037999368255';
const TIKTOK_PIXEL_ID    = '';
const GA4_ID             = '';
const GADS_ID            = '';
const GADS_CONTACT_LABEL = '';
const FB_DOMAIN_VERIFICATION   = '';
const GOOGLE_SITE_VERIFICATION = '';

/** Mã sự kiện PageView của lượt xem hiện tại (dùng chung cho Pixel và Conversions API). */
function page_view_event_id(): string
{
    static $id = null;
    return $id ??= bin2hex(random_bytes(12));
}

function capi_enabled(): bool
{
    return FB_PIXEL_ID !== '' && config_value('fb_capi_token') !== '';
}

function tracking_head(): void
{
    if (FB_DOMAIN_VERIFICATION !== '') { ?>
    <meta name="facebook-domain-verification" content="<?= htmlspecialchars(FB_DOMAIN_VERIFICATION) ?>" />
    <?php }
    if (GOOGLE_SITE_VERIFICATION !== '') { ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars(GOOGLE_SITE_VERIFICATION) ?>" />
    <?php }

    if (FB_PIXEL_ID !== '') { ?>
    <script>
      !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
      n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
      document,'script','https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '<?= FB_PIXEL_ID ?>');
      fbq('track', 'PageView', {}, { eventID: '<?= page_view_event_id() ?>' });
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
    <script>window.TAS_TRACKING = <?= json_encode([
        'adsSendTo' => $adsSendTo,
        'capi' => capi_enabled(),
        'endpoint' => parse_url(site_base_url(), PHP_URL_PATH) . 'api/track.php',
        'pageViewId' => capi_enabled() ? page_view_event_id() : '',
    ], JSON_UNESCAPED_SLASHES) ?>;</script>
    <?php
}

function tracking_body(): void
{
    if (FB_PIXEL_ID !== '') { ?>
    <noscript><img height="1" width="1" style="display:none" alt=""
      src="https://www.facebook.com/tr?id=<?= FB_PIXEL_ID ?>&ev=PageView&noscript=1" /></noscript>
    <?php }
}
