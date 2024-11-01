<div class="wrap">
    <?php screen_icon(); ?>
    <h2>SMTP King Pro</h2>
    
    <div class="kpp_block filled">
        <h2>Connect</h2>
        <div id="kpp_social">
            <div class="kpp_social facebook"><a href="https://www.facebook.com/KingProPlugins" target="_blank"><i class="icon-facebook"></i> <span class="kpp_width"><span class="kpp_opacity">Facebook</span></span></a></div>
            <div class="kpp_social twitter"><a href="https://twitter.com/KingProPlugins" target="_blank"><i class="icon-twitter"></i> <span class="kpp_width"><span class="kpp_opacity">Twitter</span></span></a></div>
            <div class="kpp_social google"><a href="https://plus.google.com/b/101488033905569308183/101488033905569308183/about" target="_blank"><i class="icon-google-plus"></i> <span class="kpp_width"><span class="kpp_opacity">Google+</span></span></a></div>
        </div>
        <h4>Found an issue? Post your issue on the <a href="http://wordpress.org/support/plugin/smtp-king-pro" target="_blank">support forums</a>. If you would prefer, please email your concern to <a href="mailto:plugins@kingpro.me">plugins@kingpro.me</a></h4>   
    </div>
    
    <div class="smtpkp_tabs">
        <a class="smtpkp_smtp_settings active">SMTP Settings</a>
        <a class="smtpkp_howto">How-To</a>
        <a class="smtpkp_faq">FAQ</a>
    </div>
    
    <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') : ?>
    <div class="updated smtpkp_notice">
        <p><?php _e( "Settings have been saved", 'smtpkp_text' ); ?></p>
    </div>
    <?php elseif (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'false') : ?>
    <div class="error smtpkp_notice">
        <p><?php _e( "Settings have <strong>NOT</strong> been saved. Please try again.", 'smtpkp_text' ); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="smtpkp_sections">
        <form method="post" action="options.php">
        <?php settings_fields('smtpkp_settings'); ?>
        <?php do_settings_sections('smtpkp_settings'); ?>
        
        <?php /****** SMTP SETTINGS ******/ ?>
        <div id="smtpkp_smtp_settings" class="smtpkp_section active">
                <?php submit_button('Save Settings', 'primary', 'submit', false, array('id'=>'smtpkp_smtp_settings_top_submit')); ?>
                <table class="form-table">
                    <tr valign="top">
                    <th scope="row">SMTP Host</th>
                    <td>
                        <?php $val = get_option('smtpkp_host'); ?>
                        <input type="text" name="smtpkp_host" value="<?= $val ?>" />
                    </td>
                    <td></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row">Mail Username</th>
                    <td>
                        <?php $val = get_option('smtpkp_username'); ?>
                        <input type="text" name="smtpkp_username" value="<?= $val ?>" />
                    </td>
                    <td></td>
                    </tr>
                    
                    <tr valign="top">
                    <th scope="row">Mail Password</th>
                    <td>
                        <?php $val = get_option('smtpkp_password'); ?>
                        <input type="password" name="smtpkp_password" value="<?= $val ?>" />
                    </td>
                    <td></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row">SMTP Port</th>
                    <td>
                        <?php $val = get_option('smtpkp_port'); ?>
                        <input type="text" name="smtpkp_port" value="<?= $val ?>" />
                    </td>
                    <td></td>
                    </tr>
                    
                    <tr valign="top">
                    <th scope="row">SMTP Encryption</th>
                    <td>
                        <?php $val = get_option('smtpkp_encryption'); ?>
                        <select name='smtpkp_encryption'>
                            <option value=''<?= ($val == '') ? ' selected' : '' ?>>None</option>
                            <option value='tls'<?= ($val == 'tls') ? ' selected' : '' ?>>TLS</option>
                            <option value='ssl'<?= ($val == 'ssl') ? ' selected' : '' ?>>SSL</option>
                        </select>
                    </td>
                    <td></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row">From Email</th>
                    <td>
                        <?php $val = get_option('smtpkp_fromemail'); ?>
                        <input type="text" name="smtpkp_fromemail" value="<?= $val ?>" />
                    </td>
                    <td></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row">From Name</th>
                    <td>
                        <?php $val = get_option('smtpkp_fromname'); ?>
                        <input type="text" name="smtpkp_fromname" value="<?= $val ?>" />
                    </td>
                    <td></td>
                    </tr>

                </table>
                <?php submit_button('Save Settings', 'primary', 'submit', false, array('id'=>'smtpkp_smtp_settings_bottom_submit')); ?>
        </div>
        
        <?php /****** HOW-TO ******/ ?>
        <div id="smtpkp_howto" class="smtpkp_section">
            <h2>How To Use</h2>
            <h3>1) Set your SMTP Setting</h3>
            <p>These details are normally the same as the details you use in your email client. Your server host should be able to provide you with them if you don't have them.</p>
            
        </div>
        
        <?php /****** FAQ ******/ ?>
        <div id="smtpkp_faq" class="smtpkp_section">
            <h2>FAQ</h2>
            
            <h4>Found an issue? Post your issue on the <a href="http://wordpress.org/support/plugin/invoice-king-pro" target="_blank">support forums</a>. If you would prefer, please email your concern to <a href="mailto:plugins@kingpro.me">plugins@kingpro.me</a></h4>   
        </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    jQuery('.smtpkp_tabs a').click(function() {
        jQuery(this).parent().children('a.active').removeClass('active');
        jQuery('.smtpkp_sections').find('div.smtpkp_section.active').removeClass('active');
        
        var active = jQuery(this).attr('class');
        jQuery(this).addClass('active');
        jQuery("#"+active).addClass('active');
    });
</script>