<?php

    $installer = $this;

    $installer->startSetup();

    // Required tables
    $statusTable = $installer->getTable('sales/order_status');
    $statusStateTable = $installer->getTable('sales/order_status_state');

    // Insert statuses
    $installer->getConnection()->insertArray(
        $statusTable,
        array('status', 'label'),
        array(
            array(
                'status' => 'pending_nimble', 
                'label' => 'Pending Card Payment' // tr019
            ),
            array(
                'status' => 'denied_nimble',
                'label' => 'Denied'
            ),
            array(
                'status' => 'abandoned_nimble',
                'label' => 'Abandoned'
            ),
            array(
                'status' => 'error_nimble',
                'label' => 'Error'
            )
        )
    );

    // Insert states and mapping of statuses to states
    $installer->getConnection()->insertArray(
        $statusStateTable,
        array('status', 'state', 'is_default'),
            array(
                array(
                    'status' => 'pending_nimble', 
                    'state' => 'pending_nimble', 
                    'is_default' => 1
                ),
                array(
                    'status' => 'denied_nimble', 
                    'state' => 'denied_nimble', 
                    'is_default' => 0
                ),
                array(
                    'status' => 'abandoned_nimble', 
                    'state' => 'abandoned_nimble', 
                    'is_default' => 0
                ),
                array(
                    'status' => 'error_nimble', 
                    'state' => 'error_nimble', 
                    'is_default' => 0
                )
            )
        );

    $installer->endSetup();


