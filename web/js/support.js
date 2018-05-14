"use strict";

function getTiming(x)
{
    if(x < 0 || x > 255)
    {
        return 0;
    }
    if(x <= 80)
    {
        return x / 16;
    }
    if(x <= 120)
    {
        return x / 8 - 5;
    }
    if(x <= 160)
    {
        return x / 2 - 50;
    }
    if(x <= 190)
    {
        return x - 130;
    }
    if(x <= 235)
    {
        return 2 * x - 320;
    }
    if(x <= 245)
    {
        return 15 * x - 3375;
    }
    return 60 * x - 14400;
}

function convertToTiming(float)
{
    const timings = getTimings();
    if(float < 0)
        return 0;
    for(let i = 0; i < timings.length; i++)
    {
        if(float < timings[i]) return i-1;
    }
    return 255;
}

function getTimings()
{
    const arr = [];
    for(let i = 0; i < 256; i++)
    {
        arr[i] = getTiming(i);
    }
    return arr;
}

function getIncrementTiming(x)
{
    if(x < 0 || x > 255)
    {
        return 0;
    }
    if(x <= 60)
    {
        return x / 2;
    }
    if(x <= 90)
    {
        return x - 30;
    }
    if(x <= 126)
    {
        return 5 * x / 2 - 165;
    }
    if(x <= 156)
    {
        return 5 * x - 480;
    }
    if(x <= 196)
    {
        return 15 * x - 2040;
    }
    if(x <= 211)
    {
        return 60 * x - 10860;
    }
    if(x <= 253)
    {
        return 300 * x - 61500;
    }
    if(x === 254) return 18000;
    return 21600;
}

function convertIncrementToTiming(float)
{
    const timings = getIncrementTimings();
    if(float < 0)
        return 0;
    for(let i = 0; i < timings.length; i++)
    {
        if(float < timings[i]) return i-1;
    }
    return 255;
}

function getIncrementTimings()
{
    const arr = [];
    for(let i = 0; i < 256; i++)
    {
        arr[i] = getIncrementTiming(i);
    }
    return arr;
}