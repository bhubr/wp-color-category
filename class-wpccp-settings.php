<?php

class WP_ColorCategory_Plugin_Settings {

	function addSettingsPage() {
//		add_theme_page( 'CCP API Test', 'CCP API Test Options', 'manage_options', 'wpccp_opts', array( $this, 'wpccp_settings_api' ) );
	}

	/**
	 * Register setting, sections and fields
	 */
	function settingsInit() {
		global $wpdb;
		// Setting
		register_setting( 'wpccp_settings', 'wpccp_settings', array( $this, 'validateSettings' ) );

		// Select Taxonomy section and field
		add_settings_section( 'wpccp_select_tax', __( 'Select taxonomy', 'wpccp' ), array( $this, 'selectTaxonomyText' ), 'wpccp_main' );
		add_settings_field( 'current_taxonomy', __( 'Select taxonomy', 'wpccp' ), array( $this, 'selectTaxonomyField' ), 'wpccp_main', 'wpccp_select_tax' );

		// Assign Colors section
		add_settings_section(
			'wpccp_assign_colors',
			__( 'Assign colors to terms', 'wpccp' ),
			array( $this, 'assignColorsText' ),
			'wpccp_main'
		);

		// Assign Colors fields
		// Get taxonomies
		$taxonomies = get_taxonomies( array(), 'objects' );

		// Get each term in each taxonomy
		foreach( $taxonomies as $tax_slug => $tax_data ) {
			$taxonomy = get_taxonomy( $tax_slug );
			$termTaxRows = $wpdb->get_results( "SELECT term_id,taxonomy FROM $wpdb->term_taxonomy WHERE taxonomy='$tax_slug'");
			$termIds = array();
			foreach( $termTaxRows as $termTaxRow ) {
				$termIds[] = $termTaxRow->term_id;
			}
			$tax_terms = !empty($termIds) ? $wpdb->get_results( "SELECT t.term_id,t.name,tt.taxonomy FROM $wpdb->terms t, $wpdb->term_taxonomy tt WHERE t.term_id = tt.term_id AND t.term_id IN (" . implode(',', $termIds) . ") ORDER BY term_id ASC" ) : [];

			foreach( $tax_terms as $term ) {
				$tid = $term->term_id;
				add_settings_field( "term-$tid", $term->name, array( $this, 'assignColorsField' ), 'wpccp_main', 'wpccp_assign_colors', array( $term ) );
			}
		}

	}

	/**
	 * Select taxonomy heading text
	 */
	function selectTaxonomyText() {
		echo '<p>' . __( 'Please select a taxonomy before assigning colors to its terms', 'wpccp' ) . '</p>';
	}

	/**
	 * Select taxonomy field
	 */
	function selectTaxonomyField() {
		$options = get_option( 'wpccp_settings' );
		$taxonomies = get_taxonomies( array(), 'objects' );
		?>

		<select id='current_taxonomy' name='wpccp_settings[current_tax]'>
		<?php
		$selected_taxonomy = $options['current_tax'];
		foreach( $taxonomies as $tax_slug => $tax_data ) {
				$selected = ( $selected_taxonomy == $tax_slug ) ? " selected='selected'" : "";
				$title = $tax_data->labels->name;
				echo "\n\t\t<option value='$tax_slug'$selected>$title</option>";
		}
		?>

		</select>
		<input type='hidden' id='old_taxonomy' value='<?php echo $selected_taxonomy ?>' />

		<?php
	}

	/**
	 * Assign colors section heading text
	 */
	function assignColorsText() {
		echo '<p>' . __( 'Click on the box next to a term, to assign it a color', 'wpccp' ) . '</p>';
	}

	/**
	 * Output color selector row, allowing to assign a color to a term
	 * @param string $args Array containing a unique term
	 */
	function assignColorsField( $args ) {
		$options = get_option( 'wpccp_settings' );
		$term = $args[0];
		$field_id = 'term-' . $term->term_id;
		// Taxonomy will be added as a class attribute, so we can hide/show the enclosing table row according to which taxonomy is selected
		$taxonomy = $term->taxonomy;
		$value = isset( $options[$field_id] ) ? $options[$field_id] : "";
		$style = !empty( $value ) ? "style='background-color: #$value'" : "";
	
		echo "\n\t<input type='text' size='6' id='$field_id' name='wpccp_settings[$field_id]' class='colorpickerField $taxonomy' value='$value' $style />\n";
	}
	
	/**
	 * Validate input : not used yet... Todo : check validity of color field, for manual input
	 */
	function validateSettings( $input ) {
		return $input;
	}
	
	function getTaxonomyTermsColors( $taxonomy ) {
		$options      = get_option( 'wpccp_settings' );
		$tax_terms    = get_terms( $taxonomy, array( 'hide_empty' => false ) );
		$terms_colors = array();
		foreach( $tax_terms as $term ) {
			$tid = $term->term_id;
			// if option not set for this term, return white
			$terms_colors[$tid] = isset( $options["term-$tid"] ) ?
				$options["term-$tid"] :	'ffffff';
		}
		return $terms_colors;
	}

}