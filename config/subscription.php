<?php

return [
    /*
     * How many days before subscription expiry the renewal window opens.
     * Controls both the dashboard warning banner and the "Request Renewal"
     * button on the Subscription page. Change SUBSCRIPTION_RENEWAL_DAYS_BEFORE
     * in .env to adjust without touching code.
     */
    'renewal_days_before' => (int) env('SUBSCRIPTION_RENEWAL_DAYS_BEFORE', 2),
];
