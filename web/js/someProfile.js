$(document).ready(() => {
    addSuggestion()
    acceptSuggestion()
    denySuggestion()
    refuceSuggestion()
})

function addSuggestion() {
    $('.add_suggestion').on('click', function () {
        let id = $(this).attr('id')
        let csrfToken = $('.csrf_token').val()

        let data = {
            'csrf_token': csrfToken.trim(),
            'target_user': id
        }

        $.ajax({
            url:  '/api/addSuggestion',
            type: 'POST',
            data: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        }).then((res) => {
            let respoce = JSON.parse(res)
            if ( respoce['status'] == 'success' ) {
                $(this).css('display', 'none')
                return toastr.success('Add was success')
            }else {
                return toastr.error('This user can`t accept friends for now!!!');
            }
        }).catch(err => console.log(err))

    })
}

function acceptSuggestion() {
    $('.accept_suggestion').on('click', function () {
        let id = $(this).attr('id')
        let csrfToken = $('.csrf_token').val()

        let data = {
            'csrf_token': csrfToken.trim(),
            'userId': id
        }

        $.ajax({
            url:  '/api/acceptSuggestion',
            type: 'POST',
            data: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        }).then((res) => {
            let respoce = JSON.parse(res)

            if ( respoce['status'] === 'success' ) {
                $(this).css('display', 'none')
                return toastr.success('Accept was success')
            }else {
                return toastr.error(respoce['description']);
            }
        }).catch(err => console.log(err))

    })
}

function denySuggestion() {
    $('.deny_suggestion').on('click', function () {
        let id = $(this).attr('id')
        let csrfToken = $('.csrf_token').val()

        $.ajax({
            url:  '/api/disableSuggestion/' + id + '/' + csrfToken,
            type: 'GET',
        }).then((res) => {
            let respoce = JSON.parse(res)

            if ( respoce['status'] === 'success' ) {
                $(this).css('display', 'none')
                return toastr.success('Denied suggestion')
            }else {
                return toastr.error(respoce['description']);
            }
        }).catch(err => console.log(err))

    })
}


function refuceSuggestion() {
    $('.remove_you_suggestion').on('click', function () {
        let id = $(this).attr('id')
        let csrfToken = $('.csrf_token').val()

        $.ajax({
            url:  '/api/disableSuggestion/' + id + '/' + csrfToken,
            type: 'GET',
        }).then((res) => {
            let respoce = JSON.parse(res)

            if ( respoce['status'] === 'success' ) {
                $(this).css('display', 'none')
                return toastr.success('Denied suggestion')
            }else {
                return toastr.error(respoce['description']);
            }
        }).catch(err => console.log(err))

    })
}