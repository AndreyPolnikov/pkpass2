<?php

use PKPass\PKPass;

function pkpass_wc($order) {

    $object_id = $order->get_id();

    if (!get_post_meta($object_id, 'pkpass_create')) {

        update_post_meta($object_id, 'pkpass_create', 1);

        $pass = new PKPass('pass_henssler_dev_cert--2121494867.p12', '4xh9Wg73nESM');

        $subcompany_id = get_post_meta($object_id, 'subcompany', true);
        $subcompany = get_post($subcompany_id);
        $tour_id = get_post_meta($object_id, 'tour', true);
	    $city_tour = get_post_meta($subcompany_id, 'tour_' . $tour_id . '_place_name', true);

        $tour_data = explode('., ', gobh_get_tour_details_data($subcompany_id, $tour_id))[1];
        $billing_first_name = $order->get_billing_first_name();
        $post_title = explode('GO by Steffen Henssler ', $subcompany->post_title);
        $headerFields = [
            [
                "key" => "seat",
                "label" => "bestellnummer",
                "value" => (string)$object_id
            ]
        ];

        $data = [
            'description' => $subcompany->post_title,
            'formatVersion' => 1,
            'organizationName' => 'umlandshop',
            'passTypeIdentifier' => 'pass.com.gobyhenssler.umlandshop-dev', // Change this!
            'serialNumber' => '12345678',
            'teamIdentifier' => 'UGFVQ68KL3', // Change this!
            'backgroundColor' => '#000000',
            'foregroundColor' => 'rgb(255, 255, 255)',
            'labelColor' => 'rgb(255, 255, 255)',
            'relevantDate' => date('Y-m-d\TH:i:sP'),

            'eventTicket' => [
                "headerFields" => $headerFields,
                'primaryFields' => [

                ],
                'secondaryFields' => [
                    [
                        'key' => 'name',
                        'label' => 'store',
                        'value' => (string)$post_title[1],
                    ],
                    [
                        'key' => 'name4',
                        'label' => 'Tour',
                        'value' => (string)$city_tour,
                    ],
                ],
                'auxiliaryFields' => [
                    [
                        'key' => 'name2',
                        'label' => 'name',
                        'value' => (string)$billing_first_name,
                    ],
                    [
                        'key' => 'data',
                        'label' => 'datum',
                        'value' => (string)$tour_data,
                    ],
                ],

                'transitType' => 'PKTransitTypeAir',
            ],

            'barcode' => [
                'format' => 'PKBarcodeFormatAztec',
                'message' => site_url(). '/check-code/?order-id=' . $object_id,
                'messageEncoding' => 'iso-8859-1',
            ],
        ];

        $pass->setData($data);

// Add files to the pass package

        $pass->addFile(get_template_directory() . '/pkpass/examples/images/icon.png');
        $pass->addFile(get_template_directory() . '/pkpass/examples/images/icon@2x.png');
        $pass->addFile(get_template_directory() . '/pkpass/examples/images/logo.png');
        $pass->addFile(get_template_directory() . '/pkpass/examples/images/strip.png');

// Create and output the pass

        $pkPass = $pass->create(true);

        if (!$pkPass) {
            echo 'Error: ' . $pass->getError();
            exit();
        } else {

		 $basedir = wp_upload_dir()['basedir'];

	if(!is_dir($basedir . '/pkpass_orders')) {
		mkdir($basedir . '/pkpass_orders', 0777, false);
	}		 

   	    
	    $object_idPkpass =  md5($order->get_id());
            $name = $basedir . '/pkpass_orders/pass(' . $object_idPkpass . ').pkpass';
	    $nameSave =   '/wp-content/uploads/pkpass_orders/pass(' . $object_idPkpass . ').pkpass';
            $myfile = fopen($name, "a") or die("Unable to open file!");
            $txt = $pkPass;

            fwrite($myfile, $txt);
            fclose($myfile);

            update_post_meta($object_id, 'pkpass', $nameSave);
	        update_post_meta($object_id, 'pkpassID', $object_idPkpass);
	        update_post_meta($object_id, 'pkpassDATA', $name);

        }

    }

}
