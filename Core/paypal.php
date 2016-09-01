<?php
    $paypal = new \PayPal\Rest\ApiContext(
        new \PayPal\Auth\OAuthTokenCredential(
            'AY1G6yvSRuWDgkPPkvGGH-hVH92hfItLBKoFmqNJy_mr1IOVY9mHwKi01EjEEAE7mZjTTXZGkUFzgqdf',
            'EEI-m-RYGKmQrzNsOVWcT4uM8YwLCgeVAbEdoI0mwr4CGsBQMVko8ESt7xcXhCNoR8-2SLE2I3RlieXn'
        )
    );

