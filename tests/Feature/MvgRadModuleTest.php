<?php


use App\Models\User;
use AppFree\appfree\modules\MvgRad\Api\MvgRadModule;
use AppFree\Models\MvgradTransaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe("appfree mvgrad module", function () {
    //    it('state advances until OutputPin State', function () {
    it('MvgRadModule::getLastPin reads correctly from DB', function () {
        $m = new MvgRadModule();

        $testtransaction = new MvgradTransaction();
        $testuser = new User();
        $phone = "1234";
        $pin = "xyz";

        $testuser->mobilephone = $phone;
        $userId = "9999999";
        $testuser->id = $userId;
        $testuser->name = "name1";
        $testuser->email = "email1";
        $testuser->password = "pass1";

        $testtransaction->user_id = $userId;
        $testtransaction->pin = $pin;
        $testtransaction->type = "rental";
        $testtransaction->group_id = uuid_create();
        $testtransaction->api_id = "prod";
        $testtransaction->mvg_start_date = "2022-02-02";
        $testuser->save();
        $testtransaction->save();

        $lastpin = $m->getLastPin($phone, $userId);

        expect($lastpin)->toBe($pin);
    });
});
