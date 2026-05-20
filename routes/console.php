<?php

use App\Jobs\AutoCancelExpiredOrders;
use App\Jobs\PollOtpOrders;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new PollOtpOrders)->everyFifteenSeconds();
Schedule::job(new AutoCancelExpiredOrders)->everyMinute();
