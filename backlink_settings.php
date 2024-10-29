<?php

wp_enqueue_script('jquery');

function blinkr_get_form() {
    ?>

    <style>
        #form {
            width: 200px;
            padding: 5px;
            background-color: #3366FF;
            color: #000000;
            -moz-border-radius: 10px;
            border-radius: 10px;
            box-shadow: 3px 3px 4px #000;
        }
    </style>

    <script type="text/javascript">
        jQuery( document ).ready( function() {


            jQuery('#optin').submit( function() {

                var email = jQuery("#email").val();
                var fname = jQuery("#fname").val();
                if (email == ""){
                    alert("Please enter your email address.");
                    var error = 1;
                    return false;
                }
                var x = jQuery("#email").val();
                var atpos = x.indexOf("@");
                var dotpos = x.lastIndexOf(".");
                if (atpos < 1 || dotpos < atpos+2 || dotpos + 2 >= x.length){
                    alert("A valid email address is required.");
                    return false;
                }

                jQuery.ajax({
                    type     : 'POST',
                    dataType : 'json',
                    url      : '<?php echo get_admin_url() ?>admin-ajax.php',
                    data     : 'action=blinkr_s2&first_name=' + fname + '&email=' + email + '&k=<?php echo wp_create_nonce( get_current_user_id() . 'act' ) ?>',
                    success: function( data ){
                        if ( data.s ) {
                            window.location = '';
                        }
                    }
                });

            });






        });


    </script>

    <div id="form">
        <form action="http://dashboard.sendreach.com/index.php/lists/mg773osmcobda/subscribe" method="post" name="optin" id="optin" accept-charset="utf-8" target="_blank">
            <label>Name:</label><br />
            <input type="text" name="FNAME" value="" id="fname" style="width:98%;" /><br />
            <label>Email:</label><br />
            <input type="text" name="EMAIL" id="email" value="" style="width:98%;" /><br /><br />
            <input type="submit" value="Register & Activate" />
        </form>
    </div>


    <?php
}

$blinkr_activated = get_option( 'blinkr_activated' );

if ( '2' != $blinkr_activated ) {
    if ( '0' == $blinkr_activated ) {

        global $userdata;
        $name  = trim( $userdata->first_name . ' ' . $userdata->last_name );
        $email = trim( $userdata->user_email );
        ?>
        <div class="wrap">
            <center>
                <h2>Backlink Rechecker Registration</h2>
                <table>
                    <tr>
                        <td align="center">
                            <h3>Register to activate your FREE Backlink Rechecker WordPress plugin.</h3>
                            <p>This is our method to help improve the service and tool, so we can help YOU more.</p>
                            <p>
                                Don't worry, I promise not to spam the hell out of your poor email. I know how annoying that can be, so don't worry.
                                <br />
                                Activate below now
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <?php blinkr_get_form() ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="center"><strong>Fill the form below to register the plugin:</strong></td>
                    </tr>
                    <tr>
                        <td align="center">

                        </td>
                    </tr>
                </table>
           </center>
        </div>
<?php
    } elseif ( '1' == $blinkr_activated ) {
        $name  = get_option( 'blinkr_name' );
        $email = get_option( 'blinkr_email' );
        $msg = 'You have not clicked on the confirmation link yet. A confirmation email has been sent to you again. Please check your email and click on the confirmation link to activate the plugin.';
        if ( isset($_GET['submit_again']) && trim($_GET['submit_again']) != '' && $msg != '' ) {
            echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
        }
        ?>
        <div class="wrap">
            <div class="blinkr_register">
                <h2>Backlink Rechecker Registration</h2>
                <table>
                    <tr>
                        <td>
                            <h3>Almost Done....</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <h3>Step 1:</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>A confirmation email has been sent to your email "<?php echo $email;?>". You must click on the link inside the email to activate the plugin.</td>
                    </tr>
                    <tr>
                        <td>
                            <h3>Step 2:</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>Click on the button below to Verify and Activate the plugin.</td>
                    </tr>
                    <tr>
                        <td>
                            <div id="form">
                                <?php blinkr_get_form() ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <p>&nbsp;</p>

                <table>
                    <tr>
                        <td>
                            <h3>Troubleshooting</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>The confirmation email is not there in my inbox!</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        Dont panic! CHECK THE JUNK, spam or bulk folder of your email.
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <strong>It's not there in the junk folder either.</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        Sometimes the confirmation email takes time to arrive. Please be patient. WAIT FOR 6 HOURS AT MOST. The confirmation email should be there by then.
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <strong>6 hours and yet no sign of a confirmation email!</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>Please register again.<br /><br /></td>

                    </tr>
                    <tr>
                        <td>
                            <strong>Help! Still no confirmation email and I have already registered twice</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>Okay, please register again from the form above using a DIFFERENT EMAIL ADDRESS this time.</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <strong>But I've still got problems.</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
<?php
    }


} else {

    global $wpdb;
    $table_name = "blinkr_check_settings";

    if (isset($_GET['updated'])) {
        echo '<div class="updated fade" id="message"><p><strong>Settings saved.</strong></p></div>';
        echo '<div class="updated fade" id="message"><p><strong>'. $_GET['statustext'] .'!</strong></p></div>';
    }
    if (isset($_GET['registered'])) {
        echo '<div class="updated fade" id="message"><p><strong>Thank you for registering the plugin. It has been activated</strong></p></div>';
    }

    if (isset($_GET['error'])) {
        echo '<div class="error" id="error"><p><strong>'. $_GET['statustext'] .'!</strong></p></div>';
    }


    if (isset($_POST['blinkr_action']) && $_POST['blinkr_action'] == "save") {


                if ($_POST['blinkr_cron'] == 1) {
                    $blinkr_cron = "0";
                    wp_clear_scheduled_hook("blinkr_cron_action");
                    $statusText = 'WP-CRON is disabled';

                } else {
                    $blinkr_cron = "1";
                    wp_schedule_event(time(), "min_2", "blinkr_cron_action");
                    $statusText = 'WP-CRON is enabled';
                }

                $sql = "Select * FROM `" . $table_name ."` WHERE name = 'wpcron'";

                $wpdb->query($sql);
                $result = $wpdb->last_result;
                $result = (array) $result;


                if ($result){
                    $wpdb->query( "UPDATE ".  $table_name . " SET value = '". $blinkr_cron . "' WHERE name = 'wpcron'");
                } else {
                    $sql = "INSERT INTO `" . $table_name ."` VALUES ('', ";
                    $sql .= "'wpcron',";
                    $sql .= "'". $blinkr_cron . "')";

                    $wpdb->query($sql);
                }


                if ($_POST['blinkr_email']) {
                    if(!filter_var($_POST['blinkr_email'], FILTER_VALIDATE_EMAIL))
                    {
                        echo "<script language='JavaScript'>window.location='?page=backlink-rechecker&error=true&statustext=Wrong email address';</script>";
                        exit;
                    } else {
                        $sql = "Select * FROM `" . $table_name ."` WHERE name = 'blinkr_email'";

                        $wpdb->query($sql);
                        $result = $wpdb->last_result;
                        $result = (array) $result;


                        if ($result){
                            $wpdb->query( "UPDATE ".  $table_name . " SET value = '". $_POST['blinkr_email'] . "' WHERE name = 'blinkr_email'");
                        } else {
                            $sql = "INSERT INTO `" . $table_name ."` VALUES ('', ";
                            $sql .= "'blinkr_email',";
                            $sql .= "'". $_POST['blinkr_email'] . "')";

                            $wpdb->query($sql);
                        }
                    }
                }

                echo "<script language='JavaScript'>window.location='?page=backlink-rechecker&updated=true&statustext=" . $statusText . "';</script>";
    }

        $sql = "Select * FROM `" . $table_name ."` WHERE name = 'wpcron'";

        $wpdb->query($sql);
        $result = $wpdb->last_result;
        $result = (array) $result;

        if ($result){
           $blinkr_cron = $result[0]->value;


        }
        $sql = "Select * FROM `" . $table_name ."` WHERE name = 'blinkr_email'";

        $wpdb->query($sql);
        $result = $wpdb->last_result;
        $result = (array) $result;

        if ($result){
           $blinkr_email = $result[0]->value;

        }

    ?>

    <div class="wrap">

    <h2>Backlink Rechecker Settings</h2>

    <br clear="all"/>
    <div></div>
    <form action="" method="post" name="blinkr_settingsForm" onsubmit="return CheckFields_email();">
    <input type="hidden" name="blinkr_action" value="save" />

    <table class="form-table">
    <tbody>
        <tr valign="top">
            <td>
                <span class="description">
                    Make sure you have watched the free <a href="http://www.backlinkrechecker.com/training/" target="_blank">Training videos here</a> first.
                    <br /><br />
                    Because Backlink Rechecker does a big job for you, it can be heavy going for your web host server.  To make this OK for your server, the rechecking job is split into sections/batches of 50 backlink URLs to check.  That batch gets checked and then the next bach gets checked.                <br /><br />
                    To initiate each batch, the server must tell the script to start checking the next batch. This initiation step is done by a process called CRON.  CRON simply means a certain server task (script) run at certain times or intervals.                <br /><br />
                    By default, we suggest CRON is set to run every 3 minutes, to start the next batch of backlinks.  If your server runs slow then set the CRON to run every 10 minutes or longer.  Usually, 3 minutes is OK.

                    <br /><br />
                    By default Backlink Rechecker uses WP-CRON to run the repeated tasks, because this can be your option if you do not know how or cannot set up proper CRON tasks for your website.  Instead of running the script at set times, WP-CRON initiates the script each time a visitor goes to a page on your site.  Given that you want the script to run every 3 minutes, unless you have a highly trafficked website, it may take a few days to get enough visitors to repeatedly run the script and process all of your backlinks in full.
                    <br /><br />
                    The best option is for you to set up a proper CRON task.  This is usually accessible in your website control panel, which is usually at yourdomain.com/cpanel. If you do not know how to do this, watch the free training videos to see how <a href="http://www.backlinkrechecker.com/training/" target="_blank">here</a>
                    <br /><br />
                </span>
                &nbsp;&nbsp;&nbsp;<label><input type="checkbox" name="blinkr_cron" value="1" <?php if ($blinkr_cron != "1") echo 'checked="checked"';?>/> I have set up a new CRON task to run the script "<?php echo admin_url()?>admin-ajax.php?action=blinkr_cron" every 3 minutes and I don't want to use WP-CRON</label>
                <br />
            </td>
        </tr>
        <tr valign="top">
            <td>
                Email address to send completed Backlink Rechecker CSV file to (contains your rechecked URLs)
                <input type="text" value="<?php echo $blinkr_email ?>" name="blinkr_email" size="30"  />
                <br />
            </td>
        </tr>
    </tbody>
    </table>
    <br clear="all"/>
    <br clear="all"/>

    <p class="submit">
    <input type="submit" value="Save Changes" class="button-primary" name="Submit">
    </p>

    </form>

            <script type="text/javascript">
                function CheckFields_email() {
                    if(!document.blinkr_settingsForm.blinkr_email.value) {
                        alert("Enter Your Email!");
                        return false;
                    }
                    if (!validEmail(document.blinkr_settingsForm.blinkr_email.value)){
                        alert("Enter Correct Email!");
                        return false;
                    }
                    return true;
                }

                function validEmail(email) {
                     var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
                        return emailPattern.test(email);
                }
            </script>

    </div>

<?php
}
?>