jQuery(document).ready(function ($) {
    $ = jQuery;
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

                    if (author_input !== '') {
                        jQuery.ajax({
                            url: rank_checker.admin_url,
                            type: 'POST',
                            data: {
                                action: 'save_search',
                                author: author_input,
                                journals: data[0].succes_data,
                            },
                            success: function (response) {
                                console.log(response)
                                output = '<h6>' + response.data + '</h6>';
                                $('#error-handler').append(output);
                                $('#error-handler').show();
                                setTimeout(function () {
                                    $('#error-handler').hide();
                                }, 5000);
                            },
                            error: function (xhr, status, error) {
                                // Handle error response
                                output = '<h2>' + error + '</h2>';
                                $('#error-handler').append(output);
                                $('#error-handler').show();
                                setTimeout(function () {
                                    $('#error-handler').hide();
                                }, 5000);
                            }
                        });
                    }

                    $.each(data[0].succes_data, function (i) {
                        $data_single = data[0].succes_data;
                        output += '<div id="issn_holder">' + '<div class="title_holder">' + $data_single[i].title + '</div>';
                        output += '<div class="issn_holder_data">';
                        $.each($data_single[i].ranking, function (j) {
                            output += '<div class="issn_holder_data_columns">' + $data_single[i].ranking[j].response_cat + ' ' + '<span class="bold">' + $data_single[i].ranking[j].response_cat_rank + '</span>' + '</div>';
                        });
                        output += '</div>';
                        output += '<div class="top_rank_holder">' + '<span>Top Rank:</span>' + '<span class="bold">' + $data_single[i].max_rank + '</span>' + '</div>' + '<button  onclick="editJournal(event)" id="journal_edit" data_postID="' + $data_single[i].post_id + '" >+</button>' + '</div>';

                        //checking if has custom data saved
                        if ($data_single[i].title_of_article || $data_single[i].number_of_editors || $data_single[i].academic_year) {
                            output += '<div class="added-data">';
                            if ($data_single[i].title_of_article) {
                                output += '<span class="all-data"> Article Title:  <span class="bold">' + $data_single[i].title_of_article + '</span> </span>';
                            }
                            if ($data_single[i].number_of_editors) {
                                output += '<span class="all-data"> Number of Editors:  <span class="bold">' + $data_single[i].number_of_editors + '</span> </span>';
                            }
                            if ($data_single[i].academic_year) {
                                output += '<span class="all-data"> Academic Year:  <span class="bold">' + $data_single[i].academic_year + '</span> </span>';
                            }
                            output += '</div>';
                        }
                    });
                }
                if (data[0].failed_issn !== null) {
                    $.each(data[0].failed_issn, function (i) {
                        $data_single_issn = data[0].failed_issn;
                        output += '<div id="issn_holder" class="failed">' + '<span>' + $data_single_issn[i].failed_issn + ' not found in our database, add this by clicking the + on the top right corner' + '</span>' + '<button  onclick="addJournal(event)" id="journal_add"  data_issn="' + $data_single_issn[i].failed_issn + '">+</button>' + '</div>';
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

    if ($("body").hasClass("page-searches-sql")) {

        $('#searchButton').click(function (e) {
                e.preventDefault();

                var searchInput = $('#searchInput').val();

                $.ajax({
                    url: rank_checker.admin_url,
                    type: 'POST',
                    data: {
                        action: 'my_search_function',
                        search_input: searchInput
                    },
                    success: function (data) {
                        output = [];
                        var searchDiv = $('#searchResults');
                        searchDiv.empty();
                        var counter = 0;

                        var data_converted = JSON.parse(data);
                        $.each(data_converted, function (i) {
                            if (data_converted[i].data_of_journals !== null) {
                                counter++;

                                output += '<div class="single-search">' + '<div class="title-holder"><h2>' + data_converted[i].author + '</h2></div>';
                                output += '<ul class="searches-holder">';
                                $.each(data_converted[i].data_of_journals, function (j) {
                                    output += '<li> <span>' + data_converted[i].data_of_journals[j].title + '</span>';
                                    output += '<span> Max Rank: ' + data_converted[i].data_of_journals[j].max_rank + '</span>';

                                    if (data_converted[i].data_of_journals[j].academic_year) {
                                        output += '<span> Academic Year:  ' + data_converted[i].data_of_journals[j].academic_year + '</span>';
                                    } else {
                                        output += '<span> Academic Year not set </span>';
                                    }
                                });
                                output += '</ul>';
                                output += '</div>';
                            }

                        });
                        if (counter === 0) {
                            output += '<h2>No search found</h2>';
                        }

                        searchDiv.append(output);
                    },
                    error: function (error) {
                        output += '<h2>No search found</h2>';
                        searchDiv.append(output);
                    }
                });
            }
        )
        ;

    }

    if ($("body").hasClass("page-issn-checker")) {
        //Call the PDF watcher
        watchDivAndToggleDisplay('response_holder_issn', 'pdfButton');
    }

    console.log("Js loaded...")
})
;

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
        let type = document.getElementById('type').value;
        let publisher = document.getElementById('publisher').value;

        // Send form data via AJAX to WordPress
        jQuery.ajax({
            type: 'POST',
            url: rank_checker.admin_url,
            dataType: 'json',
            data: {
                action: 'create_journal_post',
                title: title,
                categories: selectedCategories,
                type: type,
                publisher: publisher,
            },
            success: function (response) {
                // Handle success response
                popUP.style.display = 'none';
                output = '<h2>' + response.data + '</h2>';
                $('#error-handler').append(output);
                $('#error-handler').show();
                setTimeout(function () {
                    $('#error-handler').hide();
                }, 5000);
            },
            error: function (error) {
                // Handle error response
                output = '<h2>' + error + '</h2>';
                $('#error-handler').append(output);
                $('#error-handler').show();
                setTimeout(function () {
                    $('#error-handler').hide();
                }, 5000);
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
    let academicYearInput = document.getElementById('academicYear');
    let writersNumber = writersNumberInput.value.trim();
    let articleTitle = articleTitleInput.value.trim();
    let academicYear = academicYearInput.value.trim();
    popUP.style.display = 'block';

    // Clear input field placeholders
    writersNumberInput.placeholder = '';
    articleTitleInput.placeholder = '';
    academicYear.placeholder = '';

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

                //Add the placeholder, if there is something
                if (acfFields.writers_number) {
                    writersNumberInput.placeholder = acfFields.writers_number;
                }

                //Add the placeholder, if there is something
                if (acfFields.article_title) {
                    articleTitleInput.placeholder = acfFields.article_title;
                }

                //Add the placeholder, if there is something
                if (acfFields.academic_year) {
                    academicYearInput.placeholder = acfFields.academic_year;
                }
            } else {
                console.log('Error: ' + response.data);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            output = '<h2>' + 'AJAX Error: ' + textStatus + ' - ' + errorThrown + '</h2>';
            $('#error-handler').append(output);
            $('#error-handler').show();
            setTimeout(function () {
                $('#error-handler').hide();
            }, 5000);
        }
    });


    document.getElementById('journal_list_edit_form').addEventListener('submit', function (event) {
        event.preventDefault();

        if (writersNumber !== '' || articleTitle !== '' || academicYear !== '') {
            // AJAX request to save ACF fields
            let data = {
                action: 'save_journal_fields',
                post_id: data_postID,
                writers_number: writersNumber,
                article_title: articleTitle,
                academic_year: academicYear
            };

            // Send the data to the server
            jQuery.ajax({
                url: rank_checker.admin_url,
                method: 'POST',
                data: data,
                success: function (response) {
                    if (response.success) {
                        output = '<h2>' + 'ACF fields saved successfully.' + '</h2>';
                        jQuery('#error-handler').append(output);
                        jQuery('#error-handler').show();
                        setTimeout(function () {
                            jQuery('#error-handler').hide();
                        }, 5000);
                    } else {
                        output = '<h2>' + 'Error: ' + response.data + '</h2>';
                        jQuery('#error-handler').append(output);
                        jQuery('#error-handler').show();
                        setTimeout(function () {
                            jQuery('#error-handler').hide();
                        }, 5000);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    output = '<h2>' + 'AJAX Error: ' + textStatus + ' - ' + errorThrown + '</h2>';
                    jQuery('#error-handler').append(output);
                    jQuery('#error-handler').show();
                    setTimeout(function () {
                        jQuery('#error-handler').hide();
                    }, 5000);
                }
            });
        }

        // Hide the popup after submitting
        popUP.style.display = 'none';
    });
}

//This is for showing the PDF Button, if something are in the response div
function watchDivAndToggleDisplay(watchedDivId, targetElementId) {
    var watchedDiv = document.getElementById(watchedDivId);
    var targetElement = document.getElementById(targetElementId);

    var observer = new MutationObserver(function (mutations) {
        if (watchedDiv.innerHTML.trim() === '') {
            targetElement.style.display = 'none';
        } else {
            targetElement.style.display = 'inline-block';
        }
    });

    observer.observe(watchedDiv, {childList: true, subtree: true});
}




