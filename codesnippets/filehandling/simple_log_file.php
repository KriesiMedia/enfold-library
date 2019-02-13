<?php

/**
 * This snippet is a simple log file
 */

				$upload_dir = wp_upload_dir();
				if( ! empty( $upload_dir['basedir'] ) )
				{
					$log_dirname = trailingslashit( $upload_dir['basedir'] ) . 'avia_logfile';
					if( ! file_exists( $log_dirname ) )
					{
						wp_mkdir_p( $log_dirname );
					}
					
					$logfile = trailingslashit( $log_dirname ) . 'log.txt';
					$data = date( 'd/m/Y H:i:s', time() ) . ': Add your message' . "\r\n";
					
					file_put_contents( $logfile, $data, FILE_APPEND );
				}
				
				
