import $ from "jquery";
import Plotly from 'plotly.js-dist'

$(() => {
    const companyGraph = $('#companyGraph');
    const symbol = companyGraph.data('symbol');
    
    let data = {
    }

    $.ajax({
        type: 'post',
        url: '/api/graph/' + symbol,
        data: data,
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (response) {
            console.log(response.graphData);

            let graphData = response.graphData;

            Plotly.newPlot("companyGraph", /* JSON object */ {
                "data": [
                    { 
                        x: response.graphData.x,
                        y: response.graphData.y,
                        mode: "lines",
                        name: 'value',
                    },
                    { 
                        x: response.ema.x,
                        y: response.ema.y,
                        mode: "lines",
                        name: 'EMA',
                    },
                    { 
                        x: response.crossings.x,
                        y: response.crossings.y,
                        mode: "markers",
                        name: 'crossings',
                    },
                    { 
                        x: response.maxDeviation.x,
                        y: response.maxDeviation.y,
                        mode: "markers",
                        name: 'Max deviation',
                    },
                ],
                "layout": { 
                    showlegend: true,
                    autosize: true,
                    margin: {
                        l: 40,
                        r: 0,
                        b: 40,
                        t: 20,
                        pad: 0
                      },
                    //"width": '100%', 
                    "height": 400,
                    //xaxis: {title: "Square Meters"},
                    //yaxis: {title: "Price in Millions"},
                    //title: "House Prices vs Size"
                }
            })
        },
    });
});