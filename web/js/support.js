/*
 * MIT License
 *
 * Copyright (c) 2018 Krzysztof "RouNdeL" Zdulski
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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