jQuery(document).ready(function ($) {
    console.log("Js loaded...")

    /**  Title Ajax Search **/
    jQuery('.search-on-pressed').on('click', function () {
        event.preventDefault();
        /**Getting data from input field, and Posts titles **/
        let journal_input = document.getElementById('input-field-data-holder').value;
        //console.log($journal);
        var output;

        /** Checking if the input fields is valid **/
        $.ajax({
            type: 'POST',
            url: rank_checker.admin_url,
            dataType: 'json',
            data: {
                'action': 'get_journal_data_fun',
                'title_input': journal_input
            },
            success: function (data) {
                console.log(data);

                $.each(data, function (i) {

                    $publisher = '<?= get_field(\'publisher\', data[i].ID); ?>';
                    output += '<tr><td>' + '<a href="' + data[i].guid + '">' + data[i].post_title + '</a>' /*+ '</td>'+'<td>'+$publisher +'</td>'*/ + '</tr>';
                });
                $('#response_holder').append(output);

            },
            error: function (data) {
                //console.log(data);
                //console.log(data.responseJSON.data);
                output = '<h2>' + data.responseJSON.data + '</h2>';
                $('#response_holder').append(output);
            }
        });
    });


    /**  ISSN Ajax search **/
    jQuery('.search-on-pressed-issn').on('click', function () {
        /**Getting data from input field, and Posts ISSN **/
        let issn_input = document.getElementById('input-field-data-holder-issn').value;
        let author_input = document.getElementById('input-field-author').value;
        //console.log($journal);
        var output;
        //clear old data
        $("#response_holder_issn").html("");

        /** Checking if the input fields is valid **/
        $.ajax({
            type: 'POST',
            url: rank_checker.admin_url,
            dataType: 'json',
            data: {
                'action': 'get_issn_data_fun',
                'issn_input': issn_input
            },
            success: function (data) {
                //console.log(data);
                output = [];
                $.each(data, function (i) {
                    output += '<div id="issn_holder">' + '<div class="title_holder">' + data[i].title + '</div>';
                    output += '<div class="issn_holder_data">';
                    $.each(data[i].ranking, function (j) {
                        output += '<div class="issn_holder_data_columns">' + data[i].ranking[j].response_cat + ' ' + data[i].ranking[j].response_cat_rank + '</div>';
                    });
                    output += '</div>';
                    output += '<div class="top_rank_holder">'+ '<strong>Top Rank:</strong>' +'<span>' + data[i].max_rank+ '</span>' + '</div>' + '</div>';
                });

                if(!!author_input){
                    author_output=[];
                    author_output +=  '<div id="author_holder">'+ author_input + '</div>';;
                    $('#response_holder_issn').append(author_output);
                }
                 $('#response_holder_issn').append(output);
            },
            error: function (data) {
                output = '<h2>' + data.responseJSON.data + '</h2>';
                $('#response_holder_issn').append(output);
            }
        });
    });
    console.log("Js fully loaded...")
});

