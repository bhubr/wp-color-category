<?php
$form = $this->getFormFromFile( __FILE__ ); 
$pagetitle = __( 'Category Colors', 'wpccp' );
?>

<div id="fsoptions" class="wrap" >
<?php screen_icon(); ?>
<h2><?php echo esc_html( $pagetitle ); ?></h2>
<form action="options.php" method="post">

<?php
$this->formPagesNavMenu( $form );
settings_fields( 'wpccp_settings' );
do_settings_sections( 'wpccp_main' );
?>

<p class="submit">
<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
</p>
</form>
</div>
