/*
    Key : hFhUg05J
    Secret : 3RkF3cAHfiK1mB3b4jHMAc5h
*/

$(document).ready(function () {
    // AJAX call to api
    let data;
    $.ajax( {
        url: 'test.json',
        type: 'POST',
        data: data,
        // beforeSend : function( xhr ) {
        //     xhr.setRequestHeader( "Authorization", "BEARER " + access_token );
        // },
        success: function( data ) {
            console.log(data);
        }
    } );
});

// // Basic AJAX
// $.ajax( {
//     url: 'test.json',
//     type: 'POST',
//     data: data,
//     // beforeSend : function( xhr ) {
//     //     xhr.setRequestHeader( "Authorization", "BEARER " + access_token );
//     // },
//     success: function( data ) {
//         console.log(data);
//     }
// } );