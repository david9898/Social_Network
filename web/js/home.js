$(document).ready(async function () {

    sessionStorage.setItem('listArticles', '1')
    let articleTemplate = await $.get('/TemplatesHbs/articleTemplate.hbs')

    addHandlebarsFunctionality()
    getMoreArticles(articleTemplate)
    addLike()
    magnificPopUpLikes()
    magnificPopUpComments()
    seeWhoIsLiked()
})

let isActiveEvent = true

function getMoreArticles(articleTemplate) {

    window.addEventListener('scroll', function () {

        if ( isActiveEvent ) {
            let scroll      = window.scrollY
            let innerHeight = window.innerHeight
            let allHeight   = document.body.offsetHeight
            let csrfToken   = document.getElementById('csrf_token').value
            let firstRes    = sessionStorage.getItem('listArticles')

            if ( scroll + innerHeight >= allHeight - 400 ) {
                isActiveEvent = false

                $.ajax({
                    type: 'GET',
                    url: 'getMoreArticles/' + csrfToken + '/' + firstRes
                }).then((res) => {
                    let responce = JSON.parse(res)

                    if ( responce['status'] === 'success' ) {
                        if ( responce['articles'].length === 0 ) {
                            return
                        }

                        for (let article of responce['articles']) {
                            if ( article[8] === 0 ) {
                                continue
                            }

                            let template = Handlebars.compile(articleTemplate)
                            let html = template(article)
                            $('#show_articles').append(html)
                        }
                        addLike()
                        magnificPopUpLikes()
                        seeWhoIsLiked()

                        let currentArticleList = Number(sessionStorage.getItem('listArticles'))
                        currentArticleList++
                        sessionStorage.setItem('listArticles', currentArticleList)
                        isActiveEvent = true
                        return;

                    }else {
                        toastr.error(responce['description'])
                        return;

                    }
                })
            }

        }else {
            return
        }
    })
}

function addLike() {

    $('.fa-heart').on('click', function () {
        let csrfToken = document.getElementById('csrf_token').value
        let articleId = $(this).parent().parent().parent().attr('id')

        $.ajax({
            type: 'POST',
            url: 'addLike',
            data: JSON.stringify({
                'csrf_token': csrfToken,
                'articleId': articleId
            })
        }).then((res) => {
            let responce = JSON.parse(res)

            $(this).parent().parent().children().children('.count_likes').text(responce['likes'])

            if ( responce['status'] === 'success' ) {
                $(this).addClass('liked')
            }else {
                toastr.error(responce['description'])
            }
        })
    })

}

function addHandlebarsFunctionality() {
    Handlebars.registerHelper('ifCond', function (v1, v2, options) {
        if ( options.data.root[8] === 1) {
            return options.fn(this)
        }

        return options.inverse(this)
    })
    
    Handlebars.registerHelper('makeDate', function (options) {
        let d = new Date(options.data.root[4] * 1000),
        month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2)
            month = '0' + month;
        if (day.length < 2)
            day = '0' + day;

        return [month, day, year].join('/');

    })
}

function magnificPopUpLikes() {
    $('.likes').magnificPopup({
        type: 'inline',
        midClick: true
    })
}

function magnificPopUpComments() {
    $('.comments').magnificPopup({
        type: 'inline',
        midClick: true
    })
}

function seeWhoIsLiked() {
    $('.count_likes').on('click', function () {
        let csrfToken = $('#csrf_token').val()
        let articleId = $(this).parent().parent().parent().parent().attr('id')

        $.ajax({
            type: 'GET',
            url:  'getArticleLikes/' + articleId + '/' + csrfToken
        }).then((res) => {
            let responce = JSON.parse(res)

            $('.all_likes').empty()

            for (let i = 0; i < responce['users'].length; i++) {
                $('.all_likes').append(`<p><img src="uploads/profileImages/${responce['users'][i][1]}" /> ${responce['users'][i][0]}</p>`)
            }
        })
    })
}

