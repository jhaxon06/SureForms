<?php
/**
 * Anonymous_Submit_Form
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_SureForms_Anonymous_Submit_Field_Value extends AutomatorWP_Integration_Trigger {

    public $integration = 'sureforms';
    public $trigger = 'sureforms_anonymous_submit_field_value';

    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'anonymous'         => true,
            'label'             => __( 'Guest submits a form', 'automatorwp-sureforms' ),
            'select_option'     => __( 'Guest submits <strong>a form</strong>', 'automatorwp-sureforms' ),
            'edit_label'        => sprintf( __( 'Guest submits %1$s', 'automatorwp-sureforms' ), '{post}' ),
            'log_label'         => sprintf( __( 'Guest submits %1$s', 'automatorwp-sureforms' ), '{post}' ),
            'action'            => 'srfm_after_submission_process',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 3,
            'options'           => array(
             'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Form:', 'automatorwp' ),
                    'option_none_label' => __( 'any form', 'automatorwp' ),
                    'post_type' => 'sureforms_form'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'form_field:FIELD_NAME' => array(
                        'label'     => __( 'Form field value', 'automatorwp-sureforms' ),
                        'type'      => 'text',
                        'preview'   => __( 'Form field value, replace "FIELD_NAME" by the field name', 'automatorwp-sureforms' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            )
        ) );
    
    }
    
    //responde a un evento de envio de un formulario
    public function listener($form_data  ) {
    error_log("se vale todoooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooose vale todoooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo");
        $form_id = $form_data->id;
        $user_id = get_current_user_id();
    
        if ( $user_id !== 0 ) {
            return;
        }
    
        $form_fields = automatorwp_sureforms_get_form_fields_values( $submission_data );
    
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'form_id'       => $form_id,
            'form_fields'   => $form_fields,
        ) );
    
    }
    //determinamos si un evento de disparador anonimo deberia ser activado
    public function anonymous_deserves_trigger( $deserves_trigger, $trigger, $event, $trigger_options, $automation ) { 
        if( ! isset( $event['form_id'] ) ) {
            return false;
        }
    
        if( $trigger_options['post'] !== 'any' && absint( $event['form_id'] ) !== absint( $trigger_options['post'] ) ) {
            return false;
        }
    
        return $deserves_trigger;
    
    }
    
    //registramos los ganchos (hooks)
    public function hooks() {
        add_filter( 'automatorwp_anonymous_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        parent::hooks();
    }
    //agregamos informacion adicional sobre los campos del formularios que se envia durente la presentacion del formulario al registro del log 
    function log_meta( $log_meta, $trigger, $event, $trigger_options, $automation ) {
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }
    
        $log_meta['form_fields'] = ( isset( $event['form_fields'] ) ? $event['form_fields'] : array() );
    
        return $log_meta;
    }
    //agregamos informacion adicional a los registros de log de los disparadores (triggers) cuando se activan ciertos eventos 
    public function log_fields( $log_fields, $log, $object ) {
        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }
    
        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }
    
        $log_fields['form_fields'] = array(
            'name' => __( 'Fields Submitted', 'automatorwp-sureforms' ),
            'desc' => __( 'Information about the fields values sent on this form submission.', 'automatorwp-sureforms' ),
            'type' => 'text',
        );
    
        return $log_fields;
    }
    

}

new AutomatorWP_SureForms_Anonymous_Submit_Field_Value();
