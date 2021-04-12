
<div class="main-box api-detail">
    <div class="box-body">
        <form method="post" action="options.php">
            <?php
            // This prints out all hidden setting fields.
            settings_fields( 'appmaker_key_options' );            
            do_settings_sections( 'appmaker-setting-admin' );
            submit_button($name='Activate');
            ?>
        </form>
    </div>
</div>
