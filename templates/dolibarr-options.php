<div class="wrap">
  <div class="left-content">
      
    <div class="icon32"><img src="<?php echo plugins_url( 'images/logo_32px_32px.png', dirname( __FILE__ ) ); ?>" /></div>
    <h2>ContactForm7 - Dolibarr Options</h2>
    
    <form method="post" action="options.php" id="wpcf7_dolibarr_options_form">
    
    <?php
    foreach( array_unique( get_settings_errors( 'wpcf7_dolibarr_options' ) ) as $error ) {
      if( $error['type'] == 'updated' ) {
        print '<div id="message" class="updated fade"><p><strong>' . $error['message'] . '</strong></p></div>';        
      } else {
        print '<div id="message" class="error"><p><strong>' . $error['message'] . '</strong></p></div>';                
      }
    }
    
    settings_fields( 'wpcf7_dolibarr_options' );
    do_settings_sections( 'wpcf7_dolibarr' );
    submit_button();
    ?>
    
    </form>
    
  </div>
  