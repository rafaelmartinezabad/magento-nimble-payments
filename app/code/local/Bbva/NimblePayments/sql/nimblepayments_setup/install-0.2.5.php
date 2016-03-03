<?php

  $installer = $this;

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
                'label' => 'Pending Nimble'
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
                )
            )
        );



