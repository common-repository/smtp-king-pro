<?php

function smtpkp_check_page($hook) {
    global $current_screen;
    $smtpkp_pages = array('king-pro-plugins_page_smtp-king-pro', "toplevel_page_kpp_menu");
    
    if (in_array($hook, $smtpkp_pages)) return true;
    return false;
}

add_option('smtpkp_port', 25);
add_option('smtpkp_encryption', '');
add_option('smtpkp_host', '');
add_option('smtpkp_username', '');
add_option('smtpkp_password', '');
add_option('smtpkp_fromemail', '');
add_option('smtpkp_fromname', '');

if (!function_exists('wp_mail')) {
    function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
        require_once 'class.mail.php';
        if (get_option('smtpkp_host') !== '' && get_option('smtpkp_username') !== '' && get_option('smtpkp_password') !== '') {
            $mail = New Mail(get_option('smtpkp_host'), get_option('smtpkp_username'), get_option('smtpkp_password'), get_option('smtpkp_fromemail'), get_option('smtpkp_fromname'), get_option('smtpkp_port'), get_option('smtpkp_encryption'));
            return $mail->send($to, $subject, $message, array(), $attachments);
        } else {
            // Compact the input, apply the filters, and extract them back out
            extract( apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ) );

            if ( !is_array($attachments) )
                    $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );

            global $phpmailer;

            // (Re)create it, if it's gone missing
            if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
                    require_once ABSPATH . WPINC . '/class-phpmailer.php';
                    require_once ABSPATH . WPINC . '/class-smtp.php';
                    $phpmailer = new PHPMailer( true );
            }

            // Headers
            if ( empty( $headers ) ) {
                    $headers = array();
            } else {
                    if ( !is_array( $headers ) ) {
                            // Explode the headers out, so this function can take both
                            // string headers and an array of headers.
                            $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
                    } else {
                            $tempheaders = $headers;
                    }
                    $headers = array();
                    $cc = array();
                    $bcc = array();

                    // If it's actually got contents
                    if ( !empty( $tempheaders ) ) {
                            // Iterate through the raw headers
                            foreach ( (array) $tempheaders as $header ) {
                                    if ( strpos($header, ':') === false ) {
                                            if ( false !== stripos( $header, 'boundary=' ) ) {
                                                    $parts = preg_split('/boundary=/i', trim( $header ) );
                                                    $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                                            }
                                            continue;
                                    }
                                    // Explode them out
                                    list( $name, $content ) = explode( ':', trim( $header ), 2 );

                                    // Cleanup crew
                                    $name    = trim( $name    );
                                    $content = trim( $content );

                                    switch ( strtolower( $name ) ) {
                                            // Mainly for legacy -- process a From: header if it's there
                                            case 'from':
                                                    if ( strpos($content, '<' ) !== false ) {
                                                            // So... making my life hard again?
                                                            $from_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
                                                            $from_name = str_replace( '"', '', $from_name );
                                                            $from_name = trim( $from_name );

                                                            $from_email = substr( $content, strpos( $content, '<' ) + 1 );
                                                            $from_email = str_replace( '>', '', $from_email );
                                                            $from_email = trim( $from_email );
                                                    } else {
                                                            $from_email = trim( $content );
                                                    }
                                                    break;
                                            case 'content-type':
                                                    if ( strpos( $content, ';' ) !== false ) {
                                                            list( $type, $charset ) = explode( ';', $content );
                                                            $content_type = trim( $type );
                                                            if ( false !== stripos( $charset, 'charset=' ) ) {
                                                                    $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset ) );
                                                            } elseif ( false !== stripos( $charset, 'boundary=' ) ) {
                                                                    $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset ) );
                                                                    $charset = '';
                                                            }
                                                    } else {
                                                            $content_type = trim( $content );
                                                    }
                                                    break;
                                            case 'cc':
                                                    $cc = array_merge( (array) $cc, explode( ',', $content ) );
                                                    break;
                                            case 'bcc':
                                                    $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                                                    break;
                                            default:
                                                    // Add it to our grand headers array
                                                    $headers[trim( $name )] = trim( $content );
                                                    break;
                                    }
                            }
                    }
            }

            // Empty out the values that may be set
            $phpmailer->ClearAddresses();
            $phpmailer->ClearAllRecipients();
            $phpmailer->ClearAttachments();
            $phpmailer->ClearBCCs();
            $phpmailer->ClearCCs();
            $phpmailer->ClearCustomHeaders();
            $phpmailer->ClearReplyTos();

            // From email and name
            // If we don't have a name from the input headers
            if ( !isset( $from_name ) )
                    $from_name = 'WordPress';

            /* If we don't have an email from the input headers default to wordpress@$sitename
             * Some hosts will block outgoing mail from this address if it doesn't exist but
             * there's no easy alternative. Defaulting to admin_email might appear to be another
             * option but some hosts may refuse to relay mail from an unknown domain. See
             * http://trac.wordpress.org/ticket/5007.
             */

            if ( !isset( $from_email ) ) {
                    // Get the site domain and get rid of www.
                    $sitename = strtolower( $_SERVER['SERVER_NAME'] );
                    if ( substr( $sitename, 0, 4 ) == 'www.' ) {
                            $sitename = substr( $sitename, 4 );
                    }

                    $from_email = 'wordpress@' . $sitename;
            }

            // Plugin authors can override the potentially troublesome default
            $phpmailer->From     = apply_filters( 'wp_mail_from'     , $from_email );
            $phpmailer->FromName = apply_filters( 'wp_mail_from_name', $from_name  );

            // Set destination addresses
            if ( !is_array( $to ) )
                    $to = explode( ',', $to );

            foreach ( (array) $to as $recipient ) {
                    try {
                            // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                            $recipient_name = '';
                            if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                                    if ( count( $matches ) == 3 ) {
                                            $recipient_name = $matches[1];
                                            $recipient = $matches[2];
                                    }
                            }
                            $phpmailer->AddAddress( $recipient, $recipient_name);
                    } catch ( phpmailerException $e ) {
                            continue;
                    }
            }

            // Set mail's subject and body
            $phpmailer->Subject = $subject;
            $phpmailer->Body    = $message;

            // Add any CC and BCC recipients
            if ( !empty( $cc ) ) {
                    foreach ( (array) $cc as $recipient ) {
                            try {
                                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                                    $recipient_name = '';
                                    if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                                            if ( count( $matches ) == 3 ) {
                                                    $recipient_name = $matches[1];
                                                    $recipient = $matches[2];
                                            }
                                    }
                                    $phpmailer->AddCc( $recipient, $recipient_name );
                            } catch ( phpmailerException $e ) {
                                    continue;
                            }
                    }
            }

            if ( !empty( $bcc ) ) {
                    foreach ( (array) $bcc as $recipient) {
                            try {
                                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                                    $recipient_name = '';
                                    if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                                            if ( count( $matches ) == 3 ) {
                                                    $recipient_name = $matches[1];
                                                    $recipient = $matches[2];
                                            }
                                    }
                                    $phpmailer->AddBcc( $recipient, $recipient_name );
                            } catch ( phpmailerException $e ) {
                                    continue;
                            }
                    }
            }

            // Set to use PHP's mail()
            $phpmailer->IsMail();

            // Set Content-Type and charset
            // If we don't have a content-type from the input headers
            if ( !isset( $content_type ) )
                    $content_type = 'text/plain';

            $content_type = apply_filters( 'wp_mail_content_type', $content_type );

            $phpmailer->ContentType = $content_type;

            // Set whether it's plaintext, depending on $content_type
            if ( 'text/html' == $content_type )
                    $phpmailer->IsHTML( true );

            // If we don't have a charset from the input headers
            if ( !isset( $charset ) )
                    $charset = get_bloginfo( 'charset' );

            // Set the content-type and charset
            $phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );

            // Set custom headers
            if ( !empty( $headers ) ) {
                    foreach( (array) $headers as $name => $content ) {
                            $phpmailer->AddCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
                    }

                    if ( false !== stripos( $content_type, 'multipart' ) && ! empty($boundary) )
                            $phpmailer->AddCustomHeader( sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ) );
            }

            if ( !empty( $attachments ) ) {
                    foreach ( $attachments as $attachment ) {
                            try {
                                    $phpmailer->AddAttachment($attachment);
                            } catch ( phpmailerException $e ) {
                                    continue;
                            }
                    }
            }

            do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

            // Send!
            try {
                    return $phpmailer->Send();
            } catch ( phpmailerException $e ) {
                    return false;
            }
        }
    }
}

function smtpkp_enqueue($hook) {
    if (smtpkp_check_page($hook)) :
        wp_register_style( 'smtpkp_css', plugins_url('css/smtpkingpro-styles.css', dirname(__FILE__)), false, '1.0.0' );
        wp_register_style( 'fontawesome', plugins_url('css/font-awesome.min.css', dirname(__FILE__)), false, '3.2.1');

        wp_enqueue_style( 'smtpkp_css' );
        wp_enqueue_style( 'fontawesome' );
        wp_enqueue_style( 'thickbox' );

        wp_enqueue_script( 'jquery-ui-datepicker');
        
        wp_enqueue_script( 'thickbox' );
    endif;
}
add_action( 'admin_enqueue_scripts', 'smtpkp_enqueue' );

// Add King Pro Plugins Section
if(!function_exists('find_kpp_menu_item')) {
  function find_kpp_menu_item($handle, $sub = false) {
    if(!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
      return false;
    }
    global $menu, $submenu;
    $check_menu = $sub ? $submenu : $menu;
    if(empty($check_menu)) {
      return false;
    }
    foreach($check_menu as $k => $item) {
      if($sub) {
        foreach($item as $sm) {
          if($handle == $sm[2]) {
            return true;
          }
        }
      } 
      else {
        if($handle == $item[2]) {
          return true;
        }
      }
    }
    return false;
  }
}

function smtpkp_add_parent_page() {
  if(!find_kpp_menu_item('kpp_menu')) {
    add_menu_page('King Pro Plugins','King Pro Plugins', 'manage_options', 'kpp_menu', 'kpp_menu_page');
  }
  
  add_submenu_page('kpp_menu', 'SMTP King Pro', 'SMTP King Pro', 'manage_options', 'smtp-king-pro', 'smtpkp_settings_output');
}
add_action('admin_menu', 'smtpkp_add_parent_page');

if(!function_exists('kpp_menu_page')) {
    function kpp_menu_page() {
        include 'screens/kpp.php';
    }
}

function register_smtpkp_options() {
    
    register_setting('smtpkp_settings', 'smtpkp_port');
    register_setting('smtpkp_settings', 'smtpkp_encryption');
    register_setting('smtpkp_settings', 'smtpkp_host');
    register_setting('smtpkp_settings', 'smtpkp_username');
    register_setting('smtpkp_settings', 'smtpkp_password');
    register_setting('smtpkp_settings', 'smtpkp_fromemail');
    register_setting('smtpkp_settings', 'smtpkp_fromname');
}
add_action( 'admin_init', 'register_smtpkp_options' );

function smtpkp_settings_output() {
    include 'screens/settings.php';
}
?>
