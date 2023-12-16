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
            Plotly.newPlot("companyGraph", /* JSON object */ {
                "data": [
                    { 
                        //x: response.x,
                        y: response.y,
                        // mode: "lines",
                        // type: "scatter"
                        name: 'aapl',
                    },
                    { 
                        //x: response.x,
                        y: response.yEMA,
                        name: 'EMA',
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