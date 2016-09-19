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

    // Modif pending_nimble label
    $installer->run("update sales_order_status set label = 'Pending Card Payment' where status = 'pending_nimble'");

    // Insert states and mapping of statuses to states
    $installer->getConnection()->insertArray(
        $statusStateTable,
        array('status', 'state', 'is_default'),
            array(
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