"use strict"

let url = window.location.origin

$(function() {
    // myjs.init()
    $("#delete-modal").on("show.bs.modal", function(event) {
        $('.delete-modal form').attr('action', $(event.relatedTarget).data('route'))
    })
    $("#delete-modal").on("hide.bs.modal", function() {
        $('.delete-modal form').removeAttr('action')
    })
})

let myjs = function() {

    return {
        init: function() {
            console.log('myjs is running ..')
        }
    }
}()