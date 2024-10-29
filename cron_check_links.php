<?php

    global $wpdb;

	set_time_limit(0);

    $users_file_part    = WP_PLUGIN_DIR ."/backlink-rechecker/files/";
	$link_part          = WP_PLUGIN_DIR ."/backlink-rechecker/files/";

    //use log for script
//    $GLOBALS['use_log'] = true;
//    if ( $GLOBALS['use_log'] ) {
//        $GLOBALS['processed_id']   = time();
//        $GLOBALS['log_handle']     = fopen( $users_file_part . 'log.txt', 'a+' );
//    }

	$query  = "SELECT id FROM blinkr_check_links WHERE processed = 1 LIMIT 1";
    $res    = mysql_query($query);

    //write log
    //write_log( '01 - res= ' . mysql_num_rows( $res ) );

    if ( !mysql_num_rows( $res ) ) {

	    $query  = "SELECT * FROM blinkr_check_links ORDER BY id LIMIT 1";
	    $res    = mysql_query( $query );

        //write log
        //write_log( '02 - * ' );

	    if ( $res ) {

            //write log
            //write_log( '03 - urls' );

    		$urls = mysql_fetch_assoc( $res );
    		if ( $urls ) {

                //write log
                //write_log( '04 - urls OK id=' . $urls["id"] );

    			$file_name  = str_replace( "@", "_", $urls["email"] );
				$file_name  = str_replace( ".", "_", $file_name );
				$date       = str_replace( "-", "_", $urls["date"] );
				$date       = str_replace( ":", "_", $date );
				$date       = str_replace( " ", "_", $date );

                if ( $urls["formatted"] == 1 )
                    $file_name .= "_formatted";

				$file_name .= "_" . $date;

                $query = "UPDATE blinkr_check_links SET processed = 1, date_of_change = '" . time() . "' WHERE id = '" . $urls["id"] . "'";
    			mysql_query( $query );

                //write log
                //write_log( '05 - update 1  id=' . $urls["id"] );

                if ( $urls["formatted"] == 1 ) {
                    $text = parse_urls_formatted( explode( ",", $urls["links"] ), explode( ",", $urls["search_links"] ), $urls["parse"] );
                } else {
                    $text = parse_urls( explode(",", $urls["links"] ), explode( ",", $urls["search_links"] ), $urls["parse"] );
                }

				$result = writeToFile( $users_file_part . $file_name . ".csv", $text, "a+" );

                //write log
                //write_log( '06 - file  id=' . $urls["id"] );


				echo mysql_error()."<br />";

				if ( $result ) {
					$query      = "DELETE FROM blinkr_check_links WHERE id = '" . $urls["id"] . "'";
    				$row_count  = mysql_query( $query );
                    //write log
                    //write_log( '07 - delete  id=' . $urls["id"] );
                    //$query = "UPDATE blinkr_check_links SET processed = 0 WHERE processed = 1 ";
                    //mysql_query($query);

    				echo "Delete" . date("H:i:s") . "<br />";
    				echo $query . "<br />";
    				echo mysql_error()."<br />";
				} else {
                    //write log
                    //write_log( '08 - NOT delete  id=' . $urls["id"] );

					echo "Error Delete".date("H:i:s")."<br />";
				}

				$query      = "SELECT id FROM blinkr_check_links WHERE email = '" . $urls["email"] . "' AND date = '" . $urls["date"] . "' GROUP BY email";
    			$row_count  = mysql_query( $query );

                //write log
                //write_log( ' 09 - before email  id=' . $urls["id"] );

    			if ( $row_count and mysql_num_rows( $row_count ) == 0 ) {

                    //write log
                    //write_log( '10 - send email  id=' . $urls["id"] );

    				$to         = $urls["email"];
    				$from_mail  = get_option("admin_email");
    				$from_name  = "BacklinkRechecker";
    				$subject    = "The Results from Backlink Rechecker";
    				$message    = "Hi,<br /><br />
						Attached to this email is the file containing the results from your last run of your Backlink Rechecker WordPress plugin.
						It contains the rechecked URLs of your backlinks.<br /><br />
						Thank you for using the FREE WordPress plugin Backlink Rechecker, which forms part of the training program at
						<a target='_blank' href='http://www.ExpertTeam.tv'>www.ExpertTeam.tv (opens in new window)</a>
                        <br /><br />
                        The purpose of ExpertTeam is to provide YOU with expert staff (for a fraction of the normal cost), who can do all this work for you, checking your backlinks, running your whole SEO campaign - and running your whole business for you - so YOU dont have to be doing the work.                        <br /><br />

Remember, Backlink Rechecker is just one of the tools available within the ExpertTeam.tv system, which can be run for you, handsfree.
                        <br /><br />
                        Now that you understand a bit more about how Backlink Rechecker works (which is just one small part of the whole system), you can start to see the power of having your own Expert Team helping you with the entire process and system and making you money online.
                        <br /><br />
                        Take a look now at
                        <br /><br />
                        <a href='http://www.ExpertTeam.tv'>www.ExpertTeam.tv</a>;
                        <br /><br />
                        Thanks
                        <br /><br />
                        * Duncan Elliott
                        <br /><br />
                        <a href='http://www.ExpertTeam.tv'>www.ExpertTeam.tv</a>
                        <br />
                        ";

    				$file_name = $result;

    				sendMail( $to, $from_mail, $from_name, $subject, $message, $file_name );

                    if ( file_exists( $file_name ) ) {
						unlink( $file_name );
					}
    			}
    		}
	    }
	} else {

        //write log
        //write_log( '11 - progress=1' );

		echo "Update" . date("H:i:s") . "<br />";

        $query = "UPDATE blinkr_check_links SET processed = 0 WHERE processed = 1 AND date_of_change < " . ( time() - 60 * 15 );
	    mysql_query( $query );

	    exit;
	}

    //write log
    //write_log( '12 - end' );
//    if ( $GLOBALS['use_log'] ) {
//        fclose( $GLOBALS['log_handle'] );
//    }

	echo "END" . date("H:i:s") . "<br />";

    function writeToFile( $file, $str, $rewrite = "w+" ) {
		$handle = fopen( $file, $rewrite );
		if ( $handle ) {
			fwrite( $handle, $str );
			fclose( $handle );
			return $file;
		} else {
			return false;
		}
	}

	function get_page_curl( $url ){
        sleep( 1 );

		$file_text = "";

        if ( function_exists( 'curl_init' ) ) {
            $timeout = 5;

            $session = curl_init( $url );
            curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $session, CURLOPT_CONNECTTIMEOUT, $timeout );
            curl_setopt( $session, CURLOPT_TIMEOUT, $timeout );
            curl_setopt( $session, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible)' );
            $file_text = curl_exec( $session );
            curl_close( $session );
            return $file_text;
        } else {
            $file_text = @file_get_contents( $url );
            return $file_text;
        }

	}

    function parse_urls_formatted( $sought_in_arr, $sought_for_arr, $parse ) {
        //write log
        //write_log( '05.1.1 - START parse_urls_formatted' );

        foreach( $sought_in_arr as $key => $sought_in_url ) {
            //write log
            //write_log( '05.1.2 - parse_urls_formatted  sought_in_url= ' . $sought_in_url );
            $text = get_page_curl( $sought_in_url );

            //write log
            //write_log( '05.1.3 - parse_urls_formatted' );

            if ( $text === false ) {
                //write log
                //write_log( '05.1.4 - parse_urls_formatted bad_urls= ' . $sought_in_url );

                //$bad_urls .= $sought_in_url."<br />";
                continue;
            }

            $url_result     = false;
            $description    = "";
            $title          = "";

            if ( $parse )
                foreach( $sought_for_arr as $key => $url ) {
                    //write log
                    //write_log( '05.1.5 - parse_urls_formatted' );

                    if ( preg_match_all( "/" . str_replace( "/", "\/", $url ) . "/si", $text, $links ) ) {
                        $url_result = true;
                        break;
                    }

//                    if ( is_integer( strpos( $text, $url ) ) ) {
//                        $url_result = true;
//                        break;
//                    }

                }

            if ( $url_result or ( $text and !$parse ) ) {
                //write log
                //write_log( '05.1.6 - parse_urls_formatted' );

                preg_match( "/meta.*?name=\"description\".*?content=\"(.*?)\"/si", $text, $description );
                preg_match( "/meta.*?name=\"title\".*?content=\"(.*?)\"/si", $text, $title );
                if ( !$title )
                    preg_match( "/<title>(.*?)<\/title>/si", $text, $title );
                if( !is_array( $title ) ) $title[1] = "Title Not Found";
                if( !is_array( $description ) ) $description[1] = "Description Not Found";
                $result .= $sought_in_url  ."~" . $title[1] . "~" . $description[1] . "\r\n";
            }
            //write log
            //write_log( '05.1.7 - parse_urls_formatted' );
        }

        //write log
        //write_log( '05.1.8 - END parse_urls_formatted' );

        return $result;
    }


	function parse_urls( $sought_in_arr, $sought_for_arr, $parse ) {
        //write log
        //write_log( '05.2.1 - START parse_urls' );

		foreach( $sought_in_arr as $key => $sought_in_url ) {
            //write log
            //write_log( '05.2.2 - parse_urls  sought_in_url= ' . $sought_in_url );

			$text = get_page_curl( $sought_in_url );

            //write log
            //write_log( '05.2.3 - parse_urls' );

			if ( $text === false ) {
                //$bad_urls .= $sought_in_url."<br />";
                continue;
            }

			$url_result = "";

			if ( $parse )
			    foreach( $sought_for_arr as $key => $url ) {

                    //write log
                    //write_log( '05.2.4 - parse_urls' );

				    if ( preg_match_all( "/" . str_replace( "/", "\/", $url ) . "/si", $text, $links ) ) {
					    $url_result .= $sought_in_url . "," . $url . "\r\n";
				    }

//                    if ( is_integer( strpos( $text, $url ) ) ) {
//                        $url_result .= $sought_in_url . "," . $url . "\r\n";
//                    }

			    }

			if ( $url_result or ( $text and !$parse ) ) {
                //write log
                //write_log( '05.2.5 - parse_urls' );

                $result .= $url_result;
            }
		}

        //write log
        //write_log( '05.2.6 - END parse_urls' );

		return $result;
	}

    function sendMail( $to, $from_mail, $from_name, $subject, $message, $file_name ) {
        $file   = fopen( $file_name, "rb" );
        $un     = strtoupper( uniqid( time() ) );
        $header = "From: $from_name <$from_mail>\n";
        $header .= "To: $to\n";
        $header .= "Subject: $subject\n";
        $header .= "X-Mailer: PHPMail Tool\n";
        $header .= "Reply-To: $from_mail\n";
        $header .= "Mime-Version: 1.0\n";
        $header .= "Content-Type:multipart/mixed;";
        $header .= "boundary=\"----------".$un."\"\n\n";

        $body = "------------".$un."\nContent-Type:text/html;\n";
        $body .= "Content-Transfer-Encoding: 8bit\n\n$message\n\n";
        $body .= "------------".$un."\n";
        $body .= "Content-Type: application/octet-stream;";
        $body .= "name=\"" . basename( $file_name ) . "\"\n";
        $body .= "Content-Transfer-Encoding:base64\n";
        $body .= "Content-Disposition:attachment;";
        $body .= "filename=\"" . basename( $file_name ) . "\"\n\n";
        $body .= chunk_split( base64_encode( fread( $file, filesize( $file_name ) ) ) ) . "\n";

         if( wp_mail( $to, $subject, $body, $header ) ) {
            echo "sent email";
        } else {
            echo "sent email error";
        };
    };


    //write information to plugin log
	function write_log( $message ) {
        global $use_log;

        if ( $use_log ) {
            global $processed_id, $log_handle;
            fwrite( $log_handle, $processed_id . '-' . date( '[Y-m-d H:i:s u]' ) . ' ' . microtime() . ' ' . $message . "\r\n" );
        }
	};

?>
