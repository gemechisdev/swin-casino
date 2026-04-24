<?php

namespace App\Constants;

class Status {

    const ENABLE  = 1;
    const DISABLE = 0;

    const YES = 1;
    const NO  = 0;

    const VERIFIED   = 1;
    const UNVERIFIED = 0;

    const PAYMENT_INITIATE = 0;
    const PAYMENT_SUCCESS  = 1;
    const PAYMENT_PENDING  = 2;
    const PAYMENT_REJECT   = 3;

    CONST TICKET_OPEN   = 0;
    CONST TICKET_ANSWER = 1;
    CONST TICKET_REPLY  = 2;
    CONST TICKET_CLOSE  = 3;

    CONST PRIORITY_LOW    = 1;
    CONST PRIORITY_MEDIUM = 2;
    CONST PRIORITY_HIGH   = 3;

    const USER_ACTIVE = 1;
    const USER_BAN    = 0;

    const KYC_UNVERIFIED = 0;
    const KYC_PENDING    = 2;
    const KYC_VERIFIED   = 1;

    const GOOGLE_PAY = 5001;

    const CUR_BOTH = 1;
    const CUR_TEXT = 2;
    const CUR_SYM  = 3;

    const WIN  = 1;
    const LOSS = 0;
    const PUSH = 2;

    const GAME_RUNNING  = 0;
    const GAME_FINISHED = 1;

    // Lottery Phase States
    const LOTTERY_PHASE_PENDING   = 0;
    const LOTTERY_PHASE_ACTIVE    = 1;
    const LOTTERY_PHASE_CLOSED    = 2;
    const LOTTERY_PHASE_DRAWN     = 3;
    const LOTTERY_PHASE_COMPLETED = 4;
    const LOTTERY_PHASE_CANCELLED = 5;

    // Lottery Draw Modes
    const LOTTERY_DRAW_FROM_SOLD  = 1;
    const LOTTERY_DRAW_FROM_SPACE = 2;

    // Lottery Prize Types
    const LOTTERY_PRIZE_CASH     = 1;
    const LOTTERY_PRIZE_PHYSICAL = 2;

    // Lottery Prize Amount Modes
    const LOTTERY_PRIZE_FIXED      = 1;
    const LOTTERY_PRIZE_POT_SHARE  = 2;

    // Lottery Ticket States
    const LOTTERY_TICKET_ACTIVE   = 1;
    const LOTTERY_TICKET_WINNER   = 2;
    const LOTTERY_TICKET_REFUNDED = 3;

    // Lottery Winner Delivery States
    const LOTTERY_DELIVERY_PENDING    = 0;
    const LOTTERY_DELIVERY_DISPATCHED = 1;
    const LOTTERY_DELIVERY_DELIVERED  = 2;

    // Lottery Transaction Types
    const LOTTERY_TRX_PURCHASE = 1;
    const LOTTERY_TRX_REFUND   = 2;
    const LOTTERY_TRX_WIN      = 3;

}
