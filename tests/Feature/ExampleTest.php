<?php

test('the application redirects the root url to the charge log', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('charges.index'));
});
