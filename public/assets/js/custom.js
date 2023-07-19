jQuery(document).ready(function($) {

    const figure = $('#figures');

    $(document).on('click', '#back-to-top', function() {
        let offsetTop = figure.offset().top;
        $("html, body").animate({ scrollTop: offsetTop }, 200);
    });

    // AJAX load more sur home
    $(document).on('click', '#load-more', function() {

        $(this).remove();
        $('.back-to-top').remove();
        // AJAX
        $.ajax({
            url: `/load-more`,
            method : 'GET',
            data: {
                'offset': $(this).data('offset')
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {
                // console.log(data.result)
                let html = '';
                for(trick of data.result.content)
                {
                    html += `
                        <div id="figure__${trick.figureId}" class="figure_card" style="width:300px">
                            <a href="/trick/details/${trick.figureId}">
                                <img class="card-img-top" src="${trick.figureThumbnail}" alt="Card image">
                            </a>
                            <div class="card-body trick_card">
                                <a href="/trick/details/${trick.figureId}">
                                    <h6 class="card-title">${trick.figureTitle}</h6>
                                </a>
                                <div class="edit_trash" data-trick-id="${trick.figureId}">
                                    <a href="/trick/${trick.figureId}/edit" class="edit-button"><i class="fa-solid fa-pen"></i></a>
                                    <a href="/trick/${trick.figureId}/delete" class="delete-button"><i class="fa-solid fa-trash-can"></i></a>
                                </div>
                            </div>
                        </div>
                        `
                }
                $('#load-more').remove();
                figure.append(html);

                let figuresDisplayed = figure.children().length;

                let loadMoreCanAgain = figuresDisplayed < data.result.totalCount;

                if(loadMoreCanAgain)
                {
                    figure.after('<button type="button" id="load-more" class="btn btn-primary" data-offset="' + data.result.offset + '">Charger plus</button><div id="back-to-top"><button type="button" class="back-to-top"><i class="fa-solid fa-arrow-up"></i></button></div>')
                }
                else
                {
                    figure.after('<div id="back-to-top"><button type="button" class="back-to-top"><i class="fa-solid fa-arrow-up"></i></button></div>')
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Gérez les erreurs de la requête AJAX
            }
        });
    });

    // Normal delete figure sur home
    $('.header_figure_banner .delete-button').on('click', function(e) {

        e.preventDefault();

        if (confirm("Etes-vous certain de vouloir supprimer cette figure ?") === false) {
            return;
        }

        let trickId = $(this).parent().parent().parent().data('trickId');
        location.href = '/trick/' + trickId + '/delete'

    });

    // AJAX delete figure sur home
    $('#figures .delete-button').on('click', function(e) {

        e.preventDefault();

        if (confirm("Etes-vous certain de vouloir supprimer cette figure ?") === false)
        {
            return;
        }

        let trickId = $(this).parent().data('trickId');

        // AJAX
        $.ajax({
            url: `/trick/${trickId}/ajaxdelete`,
            method : 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {

                if (data.code === 200) {
                    if(data.result.action === 'delete')
                    {
                        $('#figure__' + trickId).hide('300');

                        let messageType = data.message.messageType;

                        $('body').prepend('<div class="absolute-flash"><div class="absolute-flash-close">X</div><div class="alert alert-' + messageType + ' ' + messageType + '-message">' + data.message.messageText + '</div></div>');
                        $('.absolute-flash-close').on('click', function() {
                            $(this).parent().remove();
                        });

                        setTimeout( () => {
                            $('.absolute-flash').hide();
                        }, '2000')
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Gérez les erreurs de la requête AJAX
            }
        });
    });
});
