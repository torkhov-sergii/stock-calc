import $ from "jquery";
import Plotly from 'plotly.js-dist'

$(() => {
    let data = {
        company: 'aapl'
    }

    //12

    $.ajax({
        type: 'post',
        url: '/api/graph/aapl',
        data: data,
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (response) {
            Plotly.newPlot("graph", /* JSON object */ {
                "data": [
                    { 
                        //x: response.x,
                        y: response.y,
                        // mode: "lines",
                        // type: "scatter"
                    },
                    { 
                        //x: response.x,
                        y: response.y2,
                    },
                    { 
                        //x: response.x,
                        y: response.y3,
                    },
                    { 
                        //x: response.x,
                        y: response.y4,
                    }
                ],
                "layout": { 
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
                    // xaxis: {range: [40, 160], title: "Square Meters"},
                    //yaxis: {range: [5, 16], title: "Price in Millions"},
                    //title: "House Prices vs Size"
                }
            })
        },
    });
});