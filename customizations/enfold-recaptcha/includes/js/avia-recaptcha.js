var aviaRecaptchaWidgets = [];

aviaRecaptchaAlert = function( button ) {
    button.addEventListener( 'click', function() {
        alert( 'Are you human? Verify with reCAPTCHA first.' );
    });
}
		
aviaRecaptchaDetectHuman = function( form, action ) {
    document.body.addEventListener( 'mousemove', function( event ) {
        form.setAttribute( 'action', action );
    }, false );

    document.body.addEventListener( 'touchmove', function( event ) {
        form.setAttribute( 'action', action );
    }, false );

    document.body.addEventListener( 'keydown', function( event ) {
        if ( ( event.keyCode === 9 ) || ( event.keyCode === 13 ) ) {
            form.setAttribute( 'action', action );
        }
    }, false );
}

var aviaRecaptchaNotice = function( el ) {
    var p = document.createElement( 'p' );
    p.classList.add( 'g-recaptcha-notice' );                           
    el.parentNode.insertBefore( p, el );
};

var aviaRecaptchaPlaceholder = function( el ) {
    var p = document.createElement( "p" );
    p.classList.add( 'g-recaptcha-widget' );                           
    el.parentNode.insertBefore( p, el );
};

var aviaRecaptchaRender = function() {
    var forms = document.getElementsByTagName( 'form' );	

    for ( var i = 0; i < forms.length; i++ ) {
        var action = forms[ i ].getAttribute( 'action' );
        forms[ i ].removeAttribute( 'action' );

        if ( forms[ i ].classList && forms[ i ].classList.contains( 'avia-form-recaptcha' ) ) {		
            var submit = forms[ i ].querySelector( 'input[type="submit"]' );
            var sitekey = submit.getAttribute( 'data-sitekey' );
            var size = submit.getAttribute( 'data-size' );
            var theme = submit.getAttribute( 'data-theme' );
            var tabindex = submit.getAttribute( 'data-tabindex' );
            var callback = submit.getAttribute( 'data-callback' );
            var expired = submit.getAttribute( 'data-expired-callback' );

            submit.classList.add( 'avia-recaptcha-disabled' );
            
            aviaRecaptchaPlaceholder( submit );
            aviaRecaptchaNotice( submit );
            aviaRecaptchaDetectHuman( forms[ i ], action );
            aviaRecaptchaAlert( submit );
            
            var params = {
                'sitekey': sitekey,
                'size': size,
                'theme': theme,
                'tabindex': tabindex,
            };

            var placeholder = forms[ i ].querySelector( '.g-recaptcha-widget' );

            if ( callback && 'function' == typeof window[ callback ] ) {
                params[ 'callback' ] = window[ callback ];
            }

            if ( expired && 'function' == typeof window[ expired ] ) {
                params[ 'expired-callback' ] = window[ expired ];
            }

            var widget_id = grecaptcha.render( placeholder, params );
            submit.setAttribute( 'data-button-index', widget_id );
            aviaRecaptchaWidgets.push( widget_id );
        }
    }
};

var aviaRecaptchaSuccess = function( token ) {
    if( ! token ) return;
    aviaRecaptchaVerify( token );
};

var aviaRecaptchaVerify = function( token ) {
    if( ! token ) return;

    jQuery.ajax( {
        type: "POST",
        url: avia_framework_globals.ajaxurl,
        data: {
            g_recaptcha_token: token,
            wp_nonce: avia_recaptcha.nonce,
            action: 'avia_ajax_recaptcha_verify'
        },
        success: function() {
            var buttons = document.querySelectorAll( 'input[type="submit"]' );
            for ( var i = 0; i < buttons.length; i++ ) {
                buttons[ i ].removeAttribute( 'disabled' );
                buttons[ i ].classList.remove( 'avia-recaptcha-disabled' );
            }
        },
        error: function() {
        },
        complete: function() {
        }
    } );
}