<?php global $rtTPG; ?>
<div class="wrap">
    <h2><?php esc_html_e('The Post Grid Settings', 'the-post-grid'); ?></h2>
    <div class="rt-settings-container">
        <div class="rt-setting-title">
            <h3><?php esc_html_e('General settings', "the-post-grid") ?></h3>
        </div>
        <div class="rt-setting-content">
            <form id="rt-tpg-settings-form" onsubmit="rtTPGSettings(this); return false;">
                <div class="rt-setting-holder">
                    <?php echo $rtTPG->rtFieldGenerator($rtTPG->rtTPGSettingFields(), true); ?>
                </div>
                <p class="submit"><input type="submit" name="submit" class="rt-admin-btn button button-primary rtSaveButton"
                                         value="<?php esc_html_e('Save Changes', 'the-post-grid'); ?>"></p>
                <?php wp_nonce_field($rtTPG->nonceText(), $rtTPG->nonceId()); ?>
            </form>
            <div class="rt-response"></div>
        </div>
        <div class="rt-pro-feature-content">
            <?php rtTPG()->rt_plugin_sc_pro_information('settings'); ?>
        </div>
    </div>

</div>
