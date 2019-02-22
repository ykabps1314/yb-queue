<?php

function test1()
{
    sleep(10);
    return mt_rand(10000, 99999);
}

function yt()
{
    var_dump('1111');
    test1();
//    yield( test1() );
}

yt();