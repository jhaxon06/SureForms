<?php
/**
 * Functions
 *
 * @package     AutomatorWP\SureForms\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;



/**
 * Get form fields values
 *
 * @since 1.0.0
 *
 * @param array $fields
 *
 * @return array
 */
//procesa los campos de un formulario y devolver sus valores en un formato simplificado
function automatorwp_sureforms_get_form_fields_values( $fields ) {

    $form_fields = array();

    foreach ( $fields as $field_name => $field_value ) {

        if( is_array( $field_value ) ) {

            foreach ( $field_value as $subfield_name => $subfield_value ) {
                if( is_string( $subfield_name ) ) {
                    $form_fields[$subfield_name] = $subfield_value;
                }
            }

        } else {
            $form_fields[$field_name] = $field_value;
        }

    }

    if( function_exists( 'automatorwp_utilities_pull_array_values' ) ) {
        $form_fields = automatorwp_utilities_pull_array_values( $form_fields );
    }

    return $form_fields;

}

/**
 * Custom tags replacements
 *
 * @since 1.0.0
 *
 * @param string    $parsed_content     Content parsed
 * @param array     $replacements       Automation replacements
 * @param int       $automation_id      The automation ID
 * @param int       $user_id            The user ID
 * @param string    $content            The content to parse
 *
 * @return string
 */
function automatorwp_sureforms_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) {

    $new_replacements = array();

    $triggers = automatorwp_get_automation_triggers( $automation_id );

    foreach( $triggers as $trigger ) {

        $trigger_args = automatorwp_get_trigger( $trigger->type );

        if( $trigger_args['integration'] !== 'sureforms' ) {
            continue;
        }

        $log = automatorwp_get_user_last_completion( $trigger->id, $user_id, 'trigger' );

        if( ! $log ) {
            continue;
        }

        ct_setup_table( 'automatorwp_logs' );
        $form_fields = ct_get_object_meta( $log->id, 'form_fields', true );
        ct_reset_setup_table();

        if( ! is_array( $form_fields ) ) {
            continue;
        }

        preg_match_all( "/\{t:" . $trigger->id . ":form_field:\s*(.*?)\s*\}/", $parsed_content, $matches );
        
        if( is_array( $matches ) && isset( $matches[1] ) ) {

            foreach( $matches[1] as $field_name ) {
                if( isset( $form_fields[$field_name] ) ) {
                    $new_replacements['{t:' . $trigger->id . ':form_field:' . $field_name . '}'] = $form_fields[$field_name];
                }
            }

        }

        preg_match_all( "/\{" . $trigger->id . ":form_field:\s*(.*?)\s*\}/", $parsed_content, $matches );

        if( is_array( $matches ) && isset( $matches[1] ) ) {

            foreach( $matches[1] as $field_name ) {
                if( isset( $form_fields[$field_name] ) ) {
                    $new_replacements['{' . $trigger->id . ':form_field:' . $field_name . '}'] = $form_fields[$field_name];
                }
            }

        }

    }

    if( count( $new_replacements ) ) {

        $tags = array_keys( $new_replacements );

        $parsed_content = str_replace( $tags, $new_replacements, $parsed_content );

    }

    return $parsed_content;

}
add_filter( 'automatorwp_parse_automation_tags', 'automatorwp_sureforms_parse_automation_tags', 10, 5 );
