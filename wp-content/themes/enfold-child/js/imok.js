jQuery(document).ready(function ($) {

    /**This is for PDF Save the page **/

    $('#pdfButton').click(function () {

        var style = document.createElement('style');
        style.innerHTML = '#journal_add,#journal_edit {display: none;}';

        // Append the <style> tag to the <head> element
        document.head.appendChild(style);

        // Remove the <style> tag after 4 seconds
        setTimeout(function () {
            style.parentNode.removeChild(style);
        }, 4000);

        window.jsPDF = window.jspdf.jsPDF;

        let srcwidth = document.getElementById('response_holder_issn').scrollWidth;
        let pdf = new jsPDF('p', 'pt', 'a4');

        pdf.html(document.getElementById('response_holder_issn'), {
            html2canvas: {
                scale: 600 / srcwidth
            },
            callback: function () {
                window.open(pdf.output('dataurlnewwindow'));
            }
        });

    });

    $('#close_popup_add').click(function () {
        let popUP = document.getElementById('journal_list_add_form_popup');
        popUP.style.display = 'none';
    });

    $('#close_popup_edit').click(function () {
        let popUP2 = document.getElementById('journal_list_edit_form_popup');
        popUP2.style.display = 'none';
    });

    /**  Title Ajax Search **/
    jQuery('.search-on-pressed').on('click', function () {
        event.preventDefault();
        /**Getting data from input field, and Posts titles **/
        let journal_input = document.getElementById('input-field-data-holder').value;
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
                $.each(data, function (i) {
                    output += '<tr><td>' + '<a href="' + data[i].guid + '">' + data[i].post_title + '</a>' + '</tr>';
                });
                $('#response_holder').append(output);
            },
            error: function (data) {
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
                output = [];

                if (Array.isArray(data[0].succes_data)) {
                    $.each(data[0].succes_data, function (i) {
                        $data_single = data[0].succes_data;
                        output += '<div id="issn_holder">' + '<div class="title_holder">' + $data_single[i].title + '</div>';
                        output += '<div class="issn_holder_data">';
                        $.each($data_single[i].ranking, function (j) {
                            output += '<div class="issn_holder_data_columns">' + $data_single[i].ranking[j].response_cat + ' ' + '<span class="bold">' + $data_single[i].ranking[j].response_cat_rank + '</span>' + '</div>';
                        });
                        output += '</div>';
                        output += '<div class="top_rank_holder">' + '<span>Top Rank:</span>' + '<span class="bold">' + $data_single[i].max_rank + '</span>' + '</div>' + '<button  onclick="editJournal(event)" id="journal_edit" data_postID="' + $data_single[i].post_id + '" >+</button>' + '</div>';
                    });
                }
                if (data[0].failed_issn !== null) {
                    $.each(data[0].failed_issn, function (i) {
                        $data_single_issn = data[0].failed_issn;
                        output += '<div id="issn_holder">' + '<span>' + $data_single_issn[i].failed_issn + ' not found in Scimago' + '</span>' + '<button  onclick="addJournal(event)" id="journal_add"  data_issn="' + $data_single_issn[i].failed_issn + '">+</button>' + '</div>';
                    });
                }


                if (!!author_input) {
                    author_output = [];
                    author_output += '<div id="author_holder">' + '<span>' + 'Author: ' + '</span>' + '<span>' + author_input + '</span>' + '</div>';
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

    console.log("Js loaded...")
});

function addJournal(e) {
    let data_issn = e.target.getAttribute('data_issn');

    let popUP = document.getElementById('journal_list_add_form_popup');
    popUP.style.display = 'block';

    document.getElementById('journal_list_add_form').addEventListener('submit', function (event) {
        event.preventDefault();
        // Collect form data
        let title = document.getElementById('title').value;
        let selectedCategories = Array.from(document.getElementById('categories').options)
            .filter(option => option.selected)
            .map(option => option.value);
        let rank = document.getElementById('rank').value;
        let type = document.getElementById('type').value;
        let issn = data_issn;
        let sjr = document.getElementById('sjr').value;
        let sjr_best_quartile = document.getElementById('sjr_best_quartile').value;
        let h_index = document.getElementById('h_index').value;
        let total_docs_2021 = document.getElementById('total_docs_2021').value;
        let total_docs_3_years = document.getElementById('total_docs_3_years').value;
        let total_refs = document.getElementById('total_refs').value;
        let country = document.getElementById('country').value;
        let region = document.getElementById('region').value;
        let publisher = document.getElementById('publisher').value;
        let coverage = document.getElementById('coverage').value;

        // Send form data via AJAX to WordPress
        jQuery.ajax({
            type: 'POST',
            url: rank_checker.admin_url,
            dataType: 'json',
            data: {
                action: 'create_journal_post',
                title: title,
                categories: selectedCategories,
                rank: rank,
                type: type,
                issn: issn,
                sjr: sjr,
                sjr_best_quartile: sjr_best_quartile,
                h_index: h_index,
                total_docs_2021: total_docs_2021,
                total_docs_3_years: total_docs_3_years,
                total_refs: total_refs,
                country: country,
                region: region,
                publisher: publisher,
                coverage: coverage,
            },
            success: function (response) {
                // Handle success response
                popUP.style.display = 'none';
            },
            error: function (error) {
                // Handle error response
                console.log(error);
            }
        });
    });
}


/** This function saves the datas as a Posts ACF field*/
function editJournal(e) {

    let data_postID = e.target.getAttribute('data_postID');
    let popUP = document.getElementById('journal_list_edit_form_popup');
    let writersNumberInput = document.getElementById('editorsInput');
    let articleTitleInput = document.getElementById('titleInput');
    let writersNumber = writersNumberInput.value.trim();
    let articleTitle = articleTitleInput.value.trim();
    popUP.style.display = 'block';

    // Clear input field placeholders
    writersNumberInput.placeholder = '';
    articleTitleInput.placeholder = '';

    // AJAX request to get current ACF field values
    let data = {
        action: 'get_journal_fields',
        post_id: data_postID
    };

    // Send the data to the server
    jQuery.ajax({
        url: rank_checker.admin_url,
        method: 'POST',
        data: data,
        success: function (response) {
            if (response.success) {
                let acfFields = response.data;

                console.log(acfFields.writers_number);

                //Add the placeholder, if there is something
                if (acfFields.writers_number) {
                    writersNumberInput.placeholder = acfFields.writers_number;
                }

                //Add the placeholder, if there is something
                if (acfFields.article_title) {
                    articleTitleInput.placeholder = acfFields.article_title;
                }
            } else {
                console.log('Error: ' + response.data);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('AJAX Error: ' + textStatus + ' - ' + errorThrown);
        }
    });


    document.getElementById('journal_list_edit_form').addEventListener('submit', function (event) {
        event.preventDefault();

        if (writersNumber !== '' || articleTitle !== '') {
            // AJAX request to save ACF fields
            let data = {
                action: 'save_journal_fields',
                post_id: data_postID,
                writers_number: writersNumber,
                article_title: articleTitle
            };

            // Send the data to the server
            jQuery.ajax({
                url: rank_checker.admin_url,
                method: 'POST',
                data: data,
                success: function (response) {
                    if (response.success) {
                        console.log('ACF fields saved successfully.');
                    } else {
                        console.log('Error: ' + response.data);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('AJAX Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }

        // Hide the popup after submitting
        popUP.style.display = 'none';
    });
}


