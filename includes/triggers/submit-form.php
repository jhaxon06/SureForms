<?php
/**
 * Submit Form
 *
 * @package     AutomatorWP\Integrations\SureForms\Triggers\Submit_Form
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_SureForms_Submit_Form extends AutomatorWP_Integration_Trigger {

    public $integration = 'sureforms';
    public $trigger = 'sureforms_submit_form';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User submits a form', 'automatorwp-sureforms' ),
            'select_option'     => __( 'User submits <strong>a form</strong>', 'automatorwp-sureforms' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User submits %1$s %2$s time(s)', 'automatorwp-sureforms' ), '{post}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'User submits %1$s', 'automatorwp-sureforms' ), '{post}' ),
            'action'            => 'srfm_before_submission',
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

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $submission_id
     * @param array $form_data
     * @param \SureForms\App\Modules\Form\Form $form
     */
    public function listener($form_before_submission_data ) {
        error_log("-----------------ENTRA AL LISTENER -------------------------------");

        $form_id = $form_before_submission_data['form_id'];
        error_log('--------------->'.$form_id);
        $user_id = get_current_user_id();
        error_log('--------------->'.$user_id);
        // Login is required
        if ( $user_id === 0 ) {
            return;
        }

        $form_fields = automatorwp_sureforms_get_form_fields_values( $form_before_submission_data );
        error_log("Form data: " . print_r($form_before_submission_data, true));


        // Trigger submit form event
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'form_id'       => $form_id,
            'form_fields'   => $form_fields,
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
   /*  public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Don't deserve if post is not received
        if( ! isset( $event['form_id'] ) ) {
            return false;
        }

        // Bail if post doesn't match with the trigger option
        if( $trigger_options['post'] !== 'any' && absint( $event['form_id'] ) !== absint( $trigger_options['post'] ) ) {
            return false;
        }

        return $deserves_trigger;

    } */

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['form_fields'] = ( isset( $event['form_fields'] ) ? $event['form_fields'] : array() );

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an trigger
        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }

        // Bail if trigger type don't match this trigger
        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['form_fields'] = array(
            'name' => __( 'Fields Submitted', 'automatorwp' ),
            'desc' => __( 'Information about the fields values sent on this form submission.', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_SureForms_Submit_Form();
