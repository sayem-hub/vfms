<?php

test('home redirects to trip request portal', function () {
    $response = $this->get('/');

    $response->assertRedirect('/portal/requests');
});
