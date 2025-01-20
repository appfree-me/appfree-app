<?php


use App\Models\User;
use AppFree\appfree\modules\MvgRad\Api\MvgRadModule;
use AppFree\Models\MvgradTransactions;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

describe("appfree mvgrad module", function () {
//    it('state advances until OutputPin State', function () {
    it('MvgRadModule::getLastPin reads correctly from DB', function () {
        $m = new MvgRadModule();

        $testtransaction = new MvgradTransactions();
        $testuser = new User();
        $phone = "112233445566";
        $pin = "xyz";

        $testuser->mobilephone = $phone;
        $testuser->id = "9999999";
        $testuser->name = "name1";
        $testuser->email = "email1";
        $testuser->password = "pass1";

        $testtransaction->user_id = "9999999";

        $testtransaction->pin = $pin;
        $testtransaction->group_id = uuid_create();
        $testuser->save();
        $testtransaction->save();

        $lastpin = $m->getLastPin($phone);

        expect($lastpin)->toBe($pin);
    });
});
