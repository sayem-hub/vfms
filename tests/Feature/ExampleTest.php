<?php

test('home redirects to trip requisition portal', function () {
    $response = $this->get('/');

    $response->assertRedirect('/portal/requisitions');
});
