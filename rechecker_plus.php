<?php

global $wpdb;

	if (isset($_POST["run"]))  {
		set_time_limit(0);

        $time_of_add            = date("Y-m-d H:i:s");
		$urls_count             = 100;
        $count_urls_in_cron_DB  = 50;
		$errors                 = "";

        $users_file_part = WP_PLUGIN_DIR ."/backlink-rechecker/files/";
        $link_part = WP_PLUGIN_DIR ."/backlink-rechecker/";

		if (!isset($_POST["sought_for"]) or !isset($_POST["sought_in"])) {
			$errors .= "Enter URLs, please.<br />";
		}

		if (!$errors) {
			$result = null;
			$bad_urls = null;

			$sought_for = $_POST["sought_for"];
			$sought_for_arr = explode("\r\n", $sought_for);
			$sought_for_arr	 = array_diff($sought_for_arr, array(""));
			$sought_in = $_POST["sought_in"];
			$sought_in_arr = explode("\r\n", $sought_in);
			$sought_in_arr = array_diff($sought_in_arr, array(""));

			if (count($sought_in_arr) > $urls_count){

                if (isset($_POST["email"])) {
                    if(filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {

                        $blinkr_email = $_POST["email"];
                        $sql = "Select * FROM  blinkr_check_settings  WHERE name = 'blinkr_email'";

                        $wpdb->query($sql);
                        $res = $wpdb->last_result;
                        $res = (array) $res;


                        if ($res){
                            $wpdb->query( "UPDATE blinkr_check_settings SET value = '". $blinkr_email . "' WHERE name = 'blinkr_email'");
                        } else {
                            $sql = "INSERT INTO blinkr_check_settings VALUES ('', ";
                            $sql .= "'blinkr_email',";
                            $sql .= "'". $blinkr_email . "')";

                            $wpdb->query($sql);
                        }
                    }
                } else {
                    $sql = "Select * FROM blinkr_check_settings WHERE name = 'blinkr_email'";

                    $wpdb->query($sql);
                    $res = $wpdb->last_result;
                    $res = (array) $res;

                    if(filter_var($res[0]->value, FILTER_VALIDATE_EMAIL))
                        $blinkr_email = $res[0]->value;
                }

                if (isset($blinkr_email) and isset($_POST["for_cron"])) {

                    //add new column to DB table
                    if ( 1 != $wpdb->query( "DESCRIBE blinkr_check_links date_of_change" ) ) {
                        $wpdb->query( "ALTER TABLE blinkr_check_links ADD date_of_change int(11) NOT NULL default '0'" );
                    }

                    $cron_run = 1;

					$sought_in_arr = array_chunk($sought_in_arr, $count_urls_in_cron_DB);

					$sought_for_arr = implode(",", $sought_for_arr);
					if (isset($_POST["dont_recheck"]) and ($_POST["dont_recheck"] != "")) $parse = 0; else $parse = 1;
					foreach ($sought_in_arr as $k => $url_list) {
						$url_list = implode(",", $url_list);
						$query = "INSERT INTO blinkr_check_links VALUES ('', '". $blinkr_email ."', '".$sought_for_arr."', '".$url_list."', '".$parse."', 0, 1, '" . $time_of_add . "', 0)";
                        if (!$wpdb->query($query)) {
    						echo "insert error!!!";
    						exit;
    					}
					}
				} else {
					$message = true;
				}
			} else {
				$message = null;
				$file_name = "check_urls_formatted";
				foreach($sought_in_arr as $key => $sought_in_url) {
					$text = blinkr_get_page_curl($sought_in_url);
					if ($text === false)
						$bad_urls .= $sought_in_url."<br />";
					$url_result = false;
					$description = "";
					$title = "";
					if (!isset($_POST["dont_recheck"]) or !$_POST["dont_recheck"])
					foreach($sought_for_arr as $key => $url) {
						if (preg_match_all("/".str_replace("/", "\/", $url)."/si", $text, $links)) {
							$url_result = true;
						}
					}
					if ($url_result or ($text and $_POST["dont_recheck"])) {
						preg_match("/meta.*?name=\"description\".*?content=\"(.*?)\"/si", $text, $description);
						preg_match("/meta.*?name=\"title\".*?content=\"(.*?)\"/si", $text, $title);
						if (!$title)
							preg_match("/<title>(.*?)<\/title>/si", $text, $title);
						if(!is_array($title)) $title[1] = "Title Not Found";
						if(!is_array($description)) $description[1] = "Description Not Found";
						$result .= $sought_in_url."~".$title[1]."~".$description[1]."\r\n";
					} else {
						$result .= "Not found\r\n";
					}
				}

				$result = blinkr_writeToFile($file_name.".csv", $result);
			}
		}
	} else {
		$sought_for = "";
		$sought_in = "";
	}

	function blinkr_get_page_curl($url){
		$file_text = "";

        $file_text = @file_get_contents($url);

        if ($file_text === false) {

            $file_text = @fgets($url);

            if ($file_text === false) {
                $ch = curl_init ($url);
                curl_setopt ($ch, CURLOPT_HEADER, 0);
                curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt ($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                $file_text = curl_exec($ch);
                curl_close ($ch);

            }
        }
		return $file_text;
	}

	function blinkr_writeToFile($file, $str){
        var_dump($file);
		$handle = fopen(WP_PLUGIN_DIR ."/backlink-rechecker/files/". $file, 'w+');
		if ($handle) {
			fwrite($handle, $str);
			fclose($handle);
			return $file;
		} else {
            return "Error";
		}
	}

	function blinkr_check_email($email){
		$p = '/^[a-z0-9!#$%&*+-=?^_`{|}~]+(\.[a-z0-9!#$%&*+-=?^_`{|}~]+)*';
        $p.= '@([-a-z0-9]+\.)+([a-z]{2,3}';
        $p.= '|info|arpa|aero|coop|name|museum|mobi)$/ix';
        return preg_match($p, $email);
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Check Backlinks URLs - Rechecker Plus</title>
	</head>
	<body>

        <?php
        if ( isset( $cron_run ) ):
        ?>
        <div id="message" class="updated">
            <p>
                <b>Backlink Rechecker</b> will now run, so long as you have CRON successfully set up to run repeatedly.<br />
                It might take a few minutes or a few hours, depending on <br />
                &nbsp;&nbsp;&nbsp;&nbsp;1. How often you set the CRON to run,<br />
                &nbsp;&nbsp;&nbsp;&nbsp;2. The speed of your server and<br />
                &nbsp;&nbsp;&nbsp;&nbsp;3. The number of backlinks to recheck.<br />
                The rechecked list will be sent to you as an attachment to an email which will go to your set email address. This email address is defined in the Backlinks Rechecker > Settings page<br />
                Remember, once you have your rechecked your backlinks, increase the power of them as outlined in the <a href="http://www.expertteam.tv">ExpertTeam.tv system</a>
            </p>
        </div>
        <?php
        endif;
        ?>

		<table border="0" cellpadding="5" cellspacing="5" align="left">
			<tr>
				<td>
					<h1>Check Backlink URLs - Rechecker Plus</h1>


<br>
Specially designed to work with and BOOST your backlinks using <a target="_blank" href="http://www.properprofits.com/pingbackoptimizer/">Pingback Optimizer</a>.  <br><br>

Check if your backlinks are live and gives you a file ready for Pingback Optimizer - automatically part of the <a target="_blank" href="http://www.expertteam.tv">ExpertTeam.tv system (opens in a new window)</a>.			</td>
			</tr>
			<?	if (isset($message)) {  ?>
				<tr>
					<td align="left" colspan="2">
                        <div style="width: 780px">
                            <p>
                            A list of more than 100 URLs need to be split up and spread out across time, over the server, so that your server does not get overloaded and crash.
                            </p>
                            <p>
                            Backlink Rechecker will do this all for you automatically - it just takes a little longer - so rather than you wait here for a while, Backlink Rechecker simply emails the file to you. It might take 10 minutes or 1 hour - it depends on how big the list is and how much demand is being put on the server.
                            </p>
                            <p>
                            To continue with this list of more than 100 you need to make sure your email address is in the Backlink Rechecker AND make sure you have the CRON task set up on your server. For help on CRON task set up watch the training tutorials <a href="http://www.backlinkrechecker.com/training" target="_blank">here</a>.
                            </p>
                            <p>
                            If you are happy that you have this all in place then click the "Continue" button now...
                            </p>
                        </div>
						<form name="check_form" method="post" action="">
                            <?php
                            if (isset($blinkr_email)) {
                            ?>
                            <label for="email" style="color: red;">CSV file will be sent on this email: </label>
                            <input type="text" readonly="readonly" value="<?php echo $blinkr_email;?>" size="30" />
                            <span class="description">You may change Email address on <a href="admin.php?page=backlink-rechecker">Settings</a> page.</span>
                            <?php
                            } else {
                            ?>
                            <label for="email" style="color: red;">Enter your email address, please: </label>
                            <input type="text" name="email" id="email" value="" onclick="return blinkr_check_email();" />
                            <span class="description">Will be saved in Settings of plugin</span>
                            <?php
                            }
                            ?>
							<input type="hidden" name="for_cron" id="for_cron" value="1" />
							<input type="hidden" name="sought_for" id="sought_for" value="<? echo $sought_for; ?>" />
							<input type="hidden" name="sought_in" id="sought_in" value="<? echo $sought_in; ?>" />
							<input type="hidden" name="dont_recheck" id="dont_recheck" value="<? echo $_POST["dont_recheck"]; ?>" />
							<br /><br /><input type="submit" value="Continue" id="run" name="run" onclick="return CheckFields_email();">
						</form>
					</td>
				</tr>
			<?	} else { ?>
				<tr>
					<td>
						<form name="check_form" method="post" action="">
							<table border="0" cellpadding="5" cellspacing="5" align="left">
								<tr>
								   <td valign="top">
								   <b>My Own Sites</b><BR>
								   <BR>
								   Copy and paste a list of our sites which should be on the backlink pages<BR>
								   <BR>
								   NOTE:  The more you put in, the slower this will run and the longer it will take you to check them<BR>
                                   <BR>
								   </td>
								   <td valign="top">
								   <b>Backlink Pages</b><BR>
								   <BR>
								   Copy and paste in your list of backlinks - Webpages on other sites where our backlink should be
								   </td>
								</tr>
								<tr>
									<td align="left" colspan="2">
										<input type="checkbox" name="dont_recheck" id="dont_recheck" value="1" onclick="return block_sought_for();">Do not recheck backlinks first - Just give me a formatted CSV
									</td>
								</tr>
								<tr>
								   <td><textarea cols="50" rows="20" name='sought_for' id='sought_for'><? echo $sought_for; ?></textarea></td>
								   <td style="width: 50%" ><textarea cols="50" rows="20" style="width: 95%" name='sought_in' id='sought_id'><? echo $sought_in; ?></textarea></td>
								</tr>
								<tr>
								   <td colspan="2"><input type="submit" value="RUN" id="run" name="run" onclick="return checkVal();"></td>
								</tr>
							</table>
						</form>
					</td>
				</tr>
				<? if (isset($result)) { ?>
					<tr>
						<td>
							<h1>Result</h1>
						</td>
					</tr>
					<tr>
						<td>
							<table border="0" cellpadding="5" cellspacing="5" width="100%" align="left">
								<tr>
									<td>
										<?
                                            if (isset($result)) {
                                                if ($result == "Error") {
                                                    echo "<span style='color: red;'>ERROR:  You need to give 'write' permissions to the folder at wp-content/plugins/backlink-rechecker/files/ <br />In your FTP program, right click on the folder and choose 'Change attributes' to 'write' for all 3 levels.</span>";
                                                } else {
                                        ?>

                                                    <form name="download_form" method="post" action="">
                                                         <input type="hidden" value="download" id="download" name="download">
                                                         <input type="hidden" value="<?=$result?>" name="file">
                                                       <br /> <input type="submit" value="Download File" name="sub">
                                                    </form>
                                        <?
                                                }
                                            }
										?>
									</td>
								</tr>
							</table>
							</td>
					</tr>
				<? } ?>
				<? if (isset($bad_urls)) { ?>
					<tr>
						<td>
							<span style='color: red;'>List of pages that was not found: </span><br /><br />
							<? echo $bad_urls ?>
						</td>
					</tr>
				<? } ?>
			<? } ?>
		</table>

		<script type="text/javascript">
			function checkVal(){
				if ((document.check_form.sought_for.value == "" && document.getElementById("sought_for").readOnly == false) || document.check_form.sought_in.value == "") {
					alert("Enter URLs, please.");
					return false;
				} else {
					return true;
				}
			}

			function block_sought_for(){
				if (document.getElementById("dont_recheck").checked){
				    document.getElementById("sought_for").value = "";
				    document.getElementById("sought_for").readOnly=true;
				    document.getElementById("sought_for").style.backgroundColor="#ebebeb";
				} else {
					document.getElementById("sought_for").readOnly=false;
					document.getElementById("sought_for").style.backgroundColor="#FFF";
				}
			}

			function CheckFields_email() {
			    if(!document.check_form.email.value) {
			        alert("Enter Your Email!");
			        return false;
			    }
                if (!validEmail(document.check_form.email.value)){
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
	</body>
</html>