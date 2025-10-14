<?php 
/**
 * Plugin Name: Enfold Complete Images Protection
 * Description: Complete image protection + multilingual warning popup - Enfold theme
 * Version: 2.0.0
 * Author: LeoTheStartegist
 */


 /**
 * ============================================================================
 * COMPLETE IMAGE PROTECTION + MULTILINGUAL WARNING POPUP - ENFOLD THEME v2.0
 * Optimized version with performance improvements and advanced features
 * Supports WPML + WordPress Locale Fallback
 * ============================================================================
 */

// CSS PROTECTION + POPUP STYLING
add_action('wp_head', 'enfold_full_protection_css', 1);
function enfold_full_protection_css() {
    if (is_admin()) return;
    
    // OPTIONAL: Exclude specific pages from protection
    // Uncomment and customize as needed:
    // if (is_page('about') || is_page('contact')) return;
    // if (is_singular('post')) return; // Disable on blog posts
    // if (is_shop() || is_product()) return; // Disable on WooCommerce
    
    ?>
    <style id="enfold-protection-css">
    /* ============================================
       GLOBAL SELECTION BLOCK
       ============================================ */
    * {
        -webkit-touch-callout: none !important;
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
    }
    
    /* ============================================
       FORM EXCEPTIONS
       Allow text selection in forms and admin bar
       ============================================ */
    input, textarea, select, [contenteditable="true"] {
        -webkit-user-select: auto !important;
        user-select: auto !important;
    }
    
    #wpadminbar, #wpadminbar * {
        -webkit-user-select: auto !important;
    }
    
    /* ============================================
       BLOCK IMAGE DRAGGING
       ============================================ */
    img, picture, figure {
        -webkit-user-drag: none !important;
        user-drag: none !important;
    }
    
    /* ============================================
       KEEP LINKS AND CONTAINERS CLICKABLE
       Important for Enfold galleries and WooCommerce
       ============================================ */
    a, button, input[type="submit"], input[type="button"], .button,
    .av-masonry-entry, .av-masonry-entry *,
    .avia-gallery, .avia-gallery *,
    .grid-entry, .grid-entry *,
    .portfolio-entry, .portfolio-entry *,
    .woocommerce-product-gallery, .woocommerce-product-gallery * {
        pointer-events: auto !important;
    }
    
    .logo, .logo *, nav, nav *, .main_menu, .main_menu * {
        pointer-events: auto !important;
    }
    
    .woocommerce form, .woocommerce form * {
        pointer-events: auto !important;
    }
    
    /* ============================================
       POPUP WARNING STYLE
       ============================================ */
    #protection-warning-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.8);
        background: rgba(0, 0, 0, 0.95);
        color: #ffffff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        z-index: 999999;
        max-width: 500px;
        width: 90%;
        text-align: center;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        opacity: 0;
        visibility: hidden;
        transform: translate(-50%, -50%) scale(0.8);
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    
    #protection-warning-popup.show {
        opacity: 1;
        visibility: visible;
        transform: translate(-50%, -50%) scale(1);
    }
    
    #protection-warning-popup .warning-icon {
        font-size: 50px;
        margin-bottom: 20px;
        display: block;
    }
    
    #protection-warning-popup .warning-title {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #ff6b6b;
    }
    
    #protection-warning-popup .warning-message {
        font-size: 16px;
        line-height: 1.6;
        color: #e0e0e0;
    }
    
    /* Close button for popup */
    #close-protection-popup {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        color: #fff;
        font-size: 28px;
        cursor: pointer;
        line-height: 1;
        width: 30px;
        height: 30px;
        padding: 0;
        transition: color 0.2s ease;
    }
    
    #close-protection-popup:hover {
        color: #ff6b6b;
    }
    
    /* Dark overlay behind popup */
    #protection-warning-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 999998;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    #protection-warning-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    
    /* ============================================
       RESPONSIVE DESIGN
       ============================================ */
    @media (max-width: 768px) {
        #protection-warning-popup {
            padding: 25px 30px;
            max-width: 90%;
        }
        
        #protection-warning-popup .warning-icon {
            font-size: 40px;
        }
        
        #protection-warning-popup .warning-title {
            font-size: 18px;
        }
        
        #protection-warning-popup .warning-message {
            font-size: 14px;
        }
    }
    </style>
    <?php
}

// JAVASCRIPT PROTECTION + POPUP
add_action('wp_footer', 'enfold_full_protection_js', 1);
function enfold_full_protection_js() {
    if (is_admin()) return;
    
    // ============================================
    // OPTIONAL: EXCLUDE SPECIFIC PAGES FROM PROTECTION
    // Uncomment and customize the examples you need
    // ============================================

    // --- EXCLUDE BY PAGE SLUG ---
    // if (is_page('about')) return; // Single page by slug
    // if (is_page('chi-siamo')) return; // Italian slug example
    // if (is_page(array('about', 'contact', 'team'))) return; // Multiple pages

    // --- EXCLUDE BY PAGE ID ---
    // if (is_page(42)) return; // Single page by ID
    // if (is_page(array(42, 108, 256))) return; // Multiple pages by ID

    // --- EXCLUDE BY PAGE TEMPLATE ---
    // if (is_page_template('page-full-width.php')) return; // Specific template
    // if (is_page_template('template-builder.php')) return; // Enfold template builder

    // --- EXCLUDE HOMEPAGE ---
    // if (is_front_page()) return; // Static homepage
    // if (is_home()) return; // Blog homepage

    // --- EXCLUDE BLOG/POSTS ---
    // if (is_singular('post')) return; // All single blog posts
    // if (is_single()) return; // Any single post (all post types)
    // if (is_single(123)) return; // Specific post by ID
    // if (is_single('my-post-slug')) return; // Specific post by slug
    // if (is_archive()) return; // All archive pages
    // if (is_category()) return; // All category archives
    // if (is_tag()) return; // All tag archives

    // --- EXCLUDE WOOCOMMERCE PAGES ---
    // if (is_shop()) return; // Shop main page
    // if (is_product()) return; // All single products
    // if (is_product_category()) return; // Product category pages
    // if (is_cart()) return; // Cart page
    // if (is_checkout()) return; // Checkout page
    // if (is_account_page()) return; // My Account page
    // if (is_woocommerce()) return; // ANY WooCommerce page

    // --- EXCLUDE CUSTOM POST TYPES ---
    // if (is_singular('portfolio')) return; // Enfold portfolio items
    // if (is_post_type_archive('portfolio')) return; // Portfolio archive
    // if (is_singular('product')) return; // WooCommerce products
    // if (is_singular(array('portfolio', 'team', 'testimonial'))) return; // Multiple CPT

    // --- EXCLUDE BY PARENT PAGE ---
    // if (is_page() && $post->post_parent == 42) return; // All children of page ID 42

    // --- EXCLUDE SEARCH RESULTS ---
    // if (is_search()) return; // Search results page

    // --- EXCLUDE 404 PAGE ---
    // if (is_404()) return; // Error 404 page

    // --- EXCLUDE BY USER ROLE ---
    // if (current_user_can('administrator')) return; // Disable for admins
    // if (current_user_can('editor')) return; // Disable for editors
    // if (is_user_logged_in()) return; // Disable for all logged users

    // --- EXCLUDE BY URL PATTERN ---
    // if (strpos($_SERVER['REQUEST_URI'], '/portfolio/') !== false) return; // URL contains /portfolio/
    // if (strpos($_SERVER['REQUEST_URI'], '/gallery/') !== false) return; // URL contains /gallery/

    // --- COMBINED CONDITIONS (AND) ---
    // if (is_page('about') && !is_user_logged_in()) return; // About page only for non-logged users
    // if (is_singular('post') && has_tag('public')) return; // Posts with 'public' tag

    // --- COMBINED CONDITIONS (OR) ---
    // if (is_page('about') || is_page('contact') || is_page('faq')) return; // Multiple pages
    // if (is_shop() || is_cart() || is_checkout()) return; // Multiple WooCommerce pages

    // --- EXCLUDE EVERYTHING EXCEPT SPECIFIC PAGES ---
    // if (!is_page(array('gallery', 'portfolio', 'photos'))) return; // Protect ONLY these pages

    // --- PRACTICAL EXAMPLES ---

    // Example 1: Disable protection on all WooCommerce pages
    // if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) return;

    // Example 2: Disable protection on contact and about pages
    // if (is_page(array('contact', 'about', 'chi-siamo'))) return;

    // Example 3: Disable protection for administrators
    // if (current_user_can('administrator')) return;

    // Example 4: Disable on homepage and blog
    // if (is_front_page() || is_home() || is_singular('post')) return;

    // Example 5: Protect ONLY gallery and portfolio pages
    // if (!is_page('gallery') && !is_singular('portfolio')) return;

    // Example 6: Disable protection on pages with specific template
    // if (is_page_template('template-blank.php')) return;

    // Example 7: Complex condition - disable on shop but NOT on products
    // if (is_shop() && !is_product()) return;
    
    // ============================================
    // DETECT CURRENT LANGUAGE
    // ============================================
    $current_lang = 'en'; // Default fallback

    // 1. Try WPML
    if (defined('ICL_LANGUAGE_CODE')) {
        $current_lang = ICL_LANGUAGE_CODE;
    }
    // 2. Try Polylang
    else if (function_exists('pll_current_language')) {
        $current_lang = pll_current_language();
    }
    // 3. Fallback to WordPress locale
    else {
        $wp_locale = get_locale();
    
        // Map WordPress locales to supported popup languages (15 languages)
        $locale_map = array(
            // Italian
            'it_IT' => 'it',
        
            // English variants
            'en_US' => 'en',
            'en_GB' => 'en',
            'en_AU' => 'en',
            'en_CA' => 'en',
            'en_NZ' => 'en',
            'en_ZA' => 'en',
        
            // German variants
            'de_DE' => 'de',
            'de_AT' => 'de',
            'de_CH' => 'de',
            'de_DE_formal' => 'de',
        
            // French variants
            'fr_FR' => 'fr',
            'fr_BE' => 'fr',
            'fr_CA' => 'fr',
            'fr_CH' => 'fr',
        
            // Spanish variants
            'es_ES' => 'es',
            'es_AR' => 'es',
            'es_CL' => 'es',
            'es_CO' => 'es',
            'es_MX' => 'es',
            'es_PE' => 'es',
            'es_VE' => 'es',
            'es_CR' => 'es',
            'es_GT' => 'es',
            'es_UY' => 'es',
        
            // Portuguese variants
            'pt_PT' => 'pt',
            'pt_BR' => 'pt',
            'pt_AO' => 'pt',
        
            // Dutch variants
            'nl_NL' => 'nl',
            'nl_BE' => 'nl',
            'nl_NL_formal' => 'nl',
        
            // Russian
            'ru_RU' => 'ru',
            'ru_UA' => 'ru',
        
            // Chinese variants
            'zh_CN' => 'zh',
            'zh_TW' => 'zh',
            'zh_HK' => 'zh',
        
            // Japanese
            'ja' => 'ja',
        
            // Arabic variants
            'ar' => 'ar',
            'ar_MA' => 'ar',
            'ar_EG' => 'ar',
            'ar_SA' => 'ar',
        
            // Turkish
            'tr_TR' => 'tr',
        
            // Polish
            'pl_PL' => 'pl',
        
            // Korean
            'ko_KR' => 'ko',
        
            // Swedish
            'sv_SE' => 'sv',
        );
    
        // Check if locale exists in map
        if (isset($locale_map[$wp_locale])) {
            $current_lang = $locale_map[$wp_locale];
        } else {
            // Fallback: extract first 2 characters
            $current_lang = substr($wp_locale, 0, 2);
        
            // Verify if extracted code is in supported languages
            $supported = array('it', 'en', 'de', 'fr', 'es', 'pt', 'nl', 'ru', 'zh', 'ja', 'ar', 'tr', 'pl', 'ko', 'sv');
            if (!in_array($current_lang, $supported)) {
                $current_lang = 'en'; // Force English if not supported
            }
        }
    }

    // Normalize to lowercase
    $current_lang = strtolower($current_lang);
    
    ?>
    <script id="enfold-protection-js">
    (function() {
        'use strict';
        
        console.log('🛡️ Enfold Full Protection v2.0 Active');
        console.log('🌍 Detected Language: <?php echo esc_js($current_lang); ?>');
        
        // ============================================
        // MULTILINGUAL MESSAGES (Extended)
        // ============================================
        var siteLanguage = '<?php echo esc_js($current_lang); ?>';
        
        var messages = {
            'it': {
                title: 'Contenuto Protetto',
                message: 'Tutte le immagini su questo sito web sono protette da copyright e non possono essere scaricate. Per questo motivo diverse funzionalità del browser sono disattivate.'
            },
            'en': {
                title: 'Protected Content',
                message: 'All images on this website are protected by copyright and cannot be downloaded. For this reason, several browser features are disabled.'
            },
            'de': {
                title: 'Geschützter Inhalt',
                message: 'Alle Bilder auf dieser Website sind urheberrechtlich geschützt und können nicht heruntergeladen werden. Aus diesem Grund sind mehrere Browser-Funktionen deaktiviert.'
            },
            'fr': {
                title: 'Contenu Protégé',
                message: 'Toutes les images de ce site sont protégées par le droit d\'auteur et ne peuvent pas être téléchargées. Pour cette raison, plusieurs fonctionnalités du navigateur sont désactivées.'
            },
            'es': {
                title: 'Contenido Protegido',
                message: 'Todas las imágenes de este sitio web están protegidas por derechos de autor y no se pueden descargar. Por esta razón, varias funciones del navegador están deshabilitadas.'
            },
            'pt': {
                title: 'Conteúdo Protegido',
                message: 'Todas as imagens neste site são protegidas por direitos autorais e não podem ser baixadas. Por este motivo, vários recursos do navegador estão desabilitados.'
            },
            'nl': {
                title: 'Beschermde Inhoud',
                message: 'Alle afbeeldingen op deze website zijn auteursrechtelijk beschermd en kunnen niet worden gedownload. Om deze reden zijn verschillende browserfuncties uitgeschakeld.'
            },
            'ru': {
                title: 'Защищенный контент',
                message: 'Все изображения на этом сайте защищены авторским правом и не могут быть загружены. По этой причине некоторые функции браузера отключены.'
            },
            'zh': {
                title: '受保护的内容',
                message: '本网站上的所有图片均受版权保护，无法下载。因此，部分浏览器功能已被禁用。'
            },
            'ja': {
                title: '保護されたコンテンツ',
                message: 'このウェブサイトのすべての画像は著作権で保護されており、ダウンロードできません。このため、いくつかのブラウザ機能が無効になっています。'
            },
            'ar': {
                title: 'محتوى محمي',
                message: 'جميع الصور على هذا الموقع محمية بحقوق النشر ولا يمكن تنزيلها. لهذا السبب، تم تعطيل العديد من ميزات المتصفح.'
            },
            'tr': {
                title: 'Korumalı İçerik',
                message: 'Bu web sitesindeki tüm görseller telif hakkı ile korunmaktadır ve indirilemez. Bu nedenle birçok tarayıcı özelliği devre dışı bırakılmıştır.'
            },
            'pl': {
                title: 'Chroniona Treść',
                message: 'Wszystkie obrazy na tej stronie są chronione prawami autorskimi i nie mogą być pobierane. Z tego powodu niektóre funkcje przeglądarki są wyłączone.'
            },
            'ko': {
                title: '보호된 콘텐츠',
                message: '이 웹사이트의 모든 이미지는 저작권으로 보호되며 다운로드할 수 없습니다. 이러한 이유로 여러 브라우저 기능이 비활성화되었습니다.'
            },
            'sv': {
                title: 'Skyddat Innehåll',
                message: 'Alla bilder på denna webbplats är skyddade av upphovsrätt och kan inte laddas ner. Av denna anledning är flera webbläsarfunktioner inaktiverade.'
            }
        };
        
        // ============================================
        // POPUP WARNING SYSTEM WITH CLOSE BUTTON
        // ============================================
        
        // Create popup HTML with close button
        var popupHTML = '<div id="protection-warning-overlay"></div>' +
                        '<div id="protection-warning-popup">' +
                        '<button id="close-protection-popup" aria-label="Close">&times;</button>' +
                        '<span class="warning-icon">⚠️</span>' +
                        '<div class="warning-title"></div>' +
                        '<div class="warning-message"></div>' +
                        '</div>';
        
        // Insert popup into DOM
        function initPopup() {
            if (!document.getElementById('protection-warning-popup')) {
                var div = document.createElement('div');
                div.innerHTML = popupHTML;
                while (div.firstChild) {
                    document.body.appendChild(div.firstChild);
                }
                
                // Add click event to close button and overlay
                var closeBtn = document.getElementById('close-protection-popup');
                var overlay = document.getElementById('protection-warning-overlay');
                
                if (closeBtn) {
                    closeBtn.addEventListener('click', hideWarningPopup);
                }
                
                if (overlay) {
                    overlay.addEventListener('click', hideWarningPopup);
                }
            }
        }
        
        // Initialize popup when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPopup);
        } else {
            initPopup();
        }
        
        // Show popup function
        var popupTimeout;
        
        function showWarningPopup() {
            // Use site language, fallback to English
            var msg = messages[siteLanguage] || messages['en'];
            
            var popup = document.getElementById('protection-warning-popup');
            var overlay = document.getElementById('protection-warning-overlay');
            
            if (popup && overlay) {
                // Set texts
                popup.querySelector('.warning-title').textContent = msg.title;
                popup.querySelector('.warning-message').textContent = msg.message;
                
                // Show popup and overlay
                overlay.classList.add('show');
                popup.classList.add('show');
                
                // Auto-close after 5 seconds
                clearTimeout(popupTimeout);
                popupTimeout = setTimeout(hideWarningPopup, 5000);
                
                console.log('⚠️ Warning shown in: ' + siteLanguage);
            }
        }
        
        // Hide popup function
        function hideWarningPopup() {
            var popup = document.getElementById('protection-warning-popup');
            var overlay = document.getElementById('protection-warning-overlay');
            
            if (popup && overlay) {
                overlay.classList.remove('show');
                popup.classList.remove('show');
            }
        }
        
        // Throttle to avoid popup spam
        var canShowPopup = true;
        
        function showWarningThrottled() {
            if (canShowPopup) {
                showWarningPopup();
                canShowPopup = false;
                
                // Re-enable after 5 seconds
                setTimeout(function() {
                    canShowPopup = true;
                }, 5000);
            }
        }
        
        // ============================================
        // BLOCK RIGHT-CLICK COMPLETELY
        // ============================================
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            showWarningThrottled();
            return false;
        }, true);
        
        // ============================================
        // BLOCK DRAG, SELECT, COPY, CUT
        // ============================================
        ['dragstart', 'drag', 'dragend', 'selectstart', 'copy', 'cut', 'paste'].forEach(function(evt) {
            document.addEventListener(evt, function(e) {
                if (e.target.tagName === 'IMG' || 
                    e.target.closest('.av-masonry-entry') ||
                    e.target.closest('.avia-gallery') ||
                    e.target.closest('.grid-entry') ||
                    e.target.closest('.portfolio-entry') ||
                    e.target.closest('.woocommerce-product-gallery')) {
                    
                    if (!e.target.closest('.logo') && !e.target.closest('#wpadminbar')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (evt === 'copy' || evt === 'cut') {
                            showWarningThrottled();
                        }
                        return false;
                    }
                }
            }, true);
        });
        
        // ============================================
        // COMPLETE KEYBOARD BLOCKING
        // ============================================
        document.addEventListener('keydown', function(e) {
            var shouldBlock = false;
            
            // F12
            if (e.keyCode === 123) {
                shouldBlock = true;
            }
            
            // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C (DevTools)
            if (e.ctrlKey && e.shiftKey && [73, 74, 67].includes(e.keyCode)) {
                shouldBlock = true;
            }
            
            // Cmd+Alt+I (Mac DevTools)
            if (e.metaKey && e.altKey && e.keyCode === 73) {
                shouldBlock = true;
            }
            
            // Ctrl+U (view source)
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 85) {
                shouldBlock = true;
            }
            
            // Ctrl+S (save)
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 83) {
                shouldBlock = true;
            }
            
            // Ctrl+C (copy) - only on images
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 67) {
                var active = document.activeElement;
                if (active && active.tagName === 'IMG') {
                    shouldBlock = true;
                }
            }
            
            // Ctrl+P (print)
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 80) {
                shouldBlock = true;
            }
            
            if (shouldBlock) {
                e.preventDefault();
                showWarningThrottled();
                return false;
            }
        }, true);
        
        // Print Screen detection and clipboard clearing
        document.addEventListener('keyup', function(e) {
            if (e.key === 'PrintScreen' || e.keyCode === 44 || e.keyCode === 42) {
                navigator.clipboard.writeText('');
                showWarningThrottled();
                console.log('🚫 Screenshot attempt detected and blocked');
            }
        });
        
        // ============================================
        // BLOCK iOS/Android LONG-PRESS
        // ============================================
        var longPressTimer = null;
        var isLongPress = false;
        var touchStartX = 0;
        var touchStartY = 0;
        
        document.addEventListener('touchstart', function(e) {
            isLongPress = false;
            
            var target = e.target;
            
            if (target.tagName === 'IMG' || 
                target.closest('.av-masonry-entry') ||
                target.closest('.avia-gallery') ||
                target.closest('.grid-entry') ||
                target.closest('.portfolio-entry') ||
                target.closest('.woocommerce-product-gallery')) {
                
                if (!target.closest('.logo') && !target.closest('#wpadminbar')) {
                    touchStartX = e.touches[0].clientX;
                    touchStartY = e.touches[0].clientY;
                    
                    target.style.webkitTouchCallout = 'none';
                    target.style.webkitUserSelect = 'none';
                    target.style.userSelect = 'none';
                    
                    longPressTimer = setTimeout(function() {
                        isLongPress = true;
                    }, 500);
                }
            }
        }, { passive: true });
        
        document.addEventListener('touchmove', function(e) {
            if (longPressTimer) {
                var moveX = Math.abs(e.touches[0].clientX - touchStartX);
                var moveY = Math.abs(e.touches[0].clientY - touchStartY);
                
                // Cancel long press if user moves finger
                if (moveX > 10 || moveY > 10) {
                    clearTimeout(longPressTimer);
                    isLongPress = false;
                }
            }
        }, { passive: true });
        
        document.addEventListener('touchend', function(e) {
            clearTimeout(longPressTimer);
            
            var target = e.target;
            
            if (isLongPress) {
                if (target.tagName === 'IMG' || 
                    target.closest('.av-masonry-entry') ||
                    target.closest('.avia-gallery') ||
                    target.closest('.grid-entry') ||
                    target.closest('.portfolio-entry') ||
                    target.closest('.woocommerce-product-gallery')) {
                    
                    if (!target.closest('.logo') && !target.closest('#wpadminbar')) {
                        e.preventDefault();
                        e.stopPropagation();
                        showWarningThrottled();
                        console.log('🚫 Long press blocked on mobile');
                    }
                }
            }
            
            isLongPress = false;
        }, { passive: false });
        
        document.addEventListener('touchcancel', function() {
            clearTimeout(longPressTimer);
            isLongPress = false;
        }, { passive: true });
        
        // ============================================
        // OPTIMIZED: APPLY PROTECTION TO ALL IMAGES
        // Uses data-protected flag to avoid re-protecting
        // ============================================
        var protectedCount = 0;
        
        function protectAllImages() {
            var imgs = document.getElementsByTagName('img');
            var newCount = 0;
            
            for (var i = 0; i < imgs.length; i++) {
                var img = imgs[i];
                
                // Skip logo and admin bar
                if (img.closest('.logo') || img.closest('#wpadminbar')) {
                    continue;
                }
                
                // Skip already protected images
                if (img.getAttribute('data-protected') === 'true') {
                    continue;
                }
                
                // Apply protection styles
                img.style.webkitUserDrag = 'none';
                img.style.webkitTouchCallout = 'none';
                img.style.webkitUserSelect = 'none';
                img.style.userSelect = 'none';
                
                // Apply protection attributes
                img.setAttribute('oncontextmenu', 'return false;');
                img.setAttribute('ondragstart', 'return false;');
                img.setAttribute('onselectstart', 'return false;');
                
                // Apply protection event handlers
                img.oncontextmenu = function(e) { 
                    e.preventDefault();
                    e.stopPropagation();
                    return false; 
                };
                
                img.ondragstart = function(e) { 
                    e.preventDefault();
                    e.stopPropagation();
                    return false; 
                };
                
                img.onselectstart = function(e) { 
                    e.preventDefault();
                    return false; 
                };
                
                // Mark as protected
                img.setAttribute('data-protected', 'true');
                newCount++;
            }
            
            if (newCount > 0) {
                protectedCount += newCount;
                console.log('🛡️ Protected ' + newCount + ' new images (Total: ' + protectedCount + ')');
            }
        }
        
        // ============================================
        // OPTIMIZED: MUTATION OBSERVER WITH DEBOUNCING
        // Monitors DOM for new images and protects them
        // ============================================
        var mutationDebounce;
        
        if (window.MutationObserver) {
            var observer = new MutationObserver(function(mutations) {
                var hasNewImages = false;
                
                // Check if any mutations contain new images
                for (var i = 0; i < mutations.length; i++) {
                    var mutation = mutations[i];
                    
                    for (var j = 0; j < mutation.addedNodes.length; j++) {
                        var node = mutation.addedNodes[j];
                        
                        if (node.nodeType === 1) {
                            if (node.tagName === 'IMG' || 
                                (node.querySelector && node.querySelector('img'))) {
                                hasNewImages = true;
                                break;
                            }
                        }
                    }
                    
                    if (hasNewImages) break;
                }
                
                // Debounce: only protect after 150ms of inactivity
                if (hasNewImages) {
                    clearTimeout(mutationDebounce);
                    mutationDebounce = setTimeout(protectAllImages, 150);
                }
            });
            
            // Start observing
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            console.log('👁️ MutationObserver active (optimized)');
        }
        
        // ============================================
        // OPTIMIZED: INITIALIZATION STRATEGY
        // Reduces redundant calls with intelligent timing
        // ============================================
        var initTimeout;
        
        function scheduleProtection() {
            clearTimeout(initTimeout);
            initTimeout = setTimeout(protectAllImages, 100);
        }
        
        // Initial protection
        scheduleProtection();
        
        // Protect on DOMContentLoaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', scheduleProtection);
        }
        
        // Protect on full load (images loaded)
        window.addEventListener('load', function() {
            scheduleProtection();
            setTimeout(protectAllImages, 1000);
        });
        
        // Protect on scroll (debounced)
        var scrollDebounce;
        window.addEventListener('scroll', function() {
            clearTimeout(scrollDebounce);
            scrollDebounce = setTimeout(protectAllImages, 300);
        }, { passive: true });
        
        // Protect on resize (debounced)
        var resizeDebounce;
        window.addEventListener('resize', function() {
            clearTimeout(resizeDebounce);
            resizeDebounce = setTimeout(protectAllImages, 300);
        }, { passive: true });
        
        console.log('✅ Full Protection v2.0 Ready with Advanced Features');
        
    })();
    </script>
    <?php
}
