<div class="logo">
    <a href="https://appmaker.xyz/wordpress/?utm_source=wordpress-plugin&utm_medium=top-bar&utm_campaign=after-plugin-install"><img src="https://storage.googleapis.com/stateless-appmaker-pages-wp/2019/02/32357a5c-c65321b4-logo.png" alt="Appmaker.xyz"/></a>
</div>
<div class="navbar">
    <ul>
        <li><a href="options-general.php?page=appmaker-wp-admin&tab=configure" class="current">Configure</a></li>
        <li><a href="options-general.php?page=appmaker-wp-admin&tab=testimonial">Testimonials</a></li>
        <li><a href="options-general.php?page=appmaker-wp-admin&tab=case-study">Case Study</a></li>
    </ul>
</div>
<?php if (empty($this->options['api_key']) ) { ?>
<div class="row">
        <div class="column main">
            <div class="main-box settings">
                <div class="box-header">
                    <h3>What's next?</h3>
                </div>
                <div class="row infograph-container">
                    <div class="column main infograph">
                        <h5>1</h5>
                        <img src="https://4c0c078479.to.intercept.rest/go-to-appmaker.png" alt="">
                        <h3>Go to <a href="https://appmaker.xyz/wordpress" target="_blank">appmaker.xyz/wordpress</a>
                        </h3>
                        <p>Configure you app with Project ID, API Key and API Secret.</p>
                    </div>
                    <div class="column main infograph">
                        <h5>2</h5>
                        <img src="https://4c0c078479.to.intercept.rest/d3a30739-buils-app.png" alt="">
                        <h3>Build your app</h3>
                        <p>Either DIY (Do it yourself) or our dedicated App team build the app for you. Just need to
                            share images and details.</p>
                    </div>
                    <div class="column main infograph">
                        <h5>3</h5>
                        <img src="https://4c0c078479.to.intercept.rest/4f330121-upload.png" alt="">
                        <h3>Publish & Promote</h3>
                        <p>We help you publish your Wordpress app to Playstore and Appstore. Quality and Performance
                            assured</p>
                    </div>
                </div>
            </div>
        </div>
</div>
<?php }?>

<div class="row">
    <div class="column main">
        <?php
        if (isset($_GET['tab']) && $_GET['tab'] == 'testimonial' ) {
            include_once 'class-appmaker-testimonial.php';
        } elseif (isset($_GET['tab']) && $_GET['tab'] == 'case-study' ) {
            include_once 'class-appmaker-casestudy.php';
        } else {
            include_once 'class-appmaker-configure.php';
        }
        ?>
    </div>
    <div class="column side">
    <?php
    if (! empty($this->options['project_id']) ) {
        $auto_login  = false;
        $button_name = 'Manage App';
        $manage_url_base = 'http://manage.appmaker.xyz';
		$manage_url      = $manage_url_base . '/apps/' . $this->options['project_id'] . '/?utm_source=wordpress-plugin&utm_medium=side-bar&utm_campaign=after-plugin-install';
        //$manage_url  = 'https://manage.appmaker.xyz/?utm_source=wordpress-plugin&utm_medium=side-bar&utm_campaign=after-plugin-install';
        if ($auto_login ) {
           // $manage_url = site_url('?rest_route=/appmaker-wc/v1/manage-login&url=' . $manage_url);
            $manage_url = site_url( '?rest_route=/appmaker-wp/v1/manage-login&url=' . $manage_url_base . '&return_to=' . '/apps/' . $this->options['project_id'] );
        }
    } else {
        $button_name = 'Visit site';
        $manage_url  = 'https://appmaker.xyz/wordpress/?utm_source=wordpress-plugin&utm_medium=side-bar&utm_campaign=after-plugin-install';
    }
    ?>
        <a href="<?php echo $manage_url; ?>" class="button-custom" target="_blank"><?php echo $button_name; ?></a>
        <div class="main-box support">
            <div class="box-header">
                <h3>Did you Know?</h3>
            </div>
            <div class="box-body ">
                <p>Read the case study of how one of our clients generated <b> over 80% </b> of sales through Mobile app alone.   <a href="https://blog.appmaker.xyz/ente-book-a-case-study/?utm_source=wordpress-plugin&utm_medium=side-bar&utm_campaign=after-plugin-install" target="_blank">(Full case study)</a> </p>
                <a href="https://blog.appmaker.xyz/?utm_source=wordpress-plugin&utm_medium=side-bar&utm_campaign=after-plugin-install" target="_blank">Blog</a>
                <a href="https://appmaker.xyz/wordpress/pricing/?utm_source=wordpress-plugin&utm_medium=side-bar&utm_campaign=after-plugin-install" target="_blank">Pricing</a>
                <a href="mailto:mail@appmaker.xyz?subject=WordPress Plugin Support" target="_top" class="box-header" style="padding: 0 0 10px 0">Email us</a>
                Follow us on :
                <ul class="social-media">
                    <li><a href="https://www.facebook.com/appmaker.xyz" style="color: #3b5999;" target="_blank">Facebook</a></li>
                    <li><a href="https://www.instagram.com/appmaker.xyz/" style="color: #e4405f;" target="_blank">Instagram</a></li>
                    <li><a href="https://twitter.com/appmaker_xyz" style="color: #55acee;" target="_blank">Twitter</a></li>
                    <li><a href="https://www.youtube.com/channel/UCYpPbibUUkhxA79dk215DdQ" style="color: #cd201f;" target="_blank">Youtube</a></li>
                </ul>
            </div>
        </div>
        <div class="main-box how-work">
            <div class="box-header">
                <h3>How do we work?</h3>
            </div>
            <div class="box-body">
                <p>You can either opt for DIY(Do it yourself) model or our App design team build a full plugin compatible app and upload it on Playstore/Appstore as per requirement for your store. </p>
            </div>
        </div>
    </div>
</div>

