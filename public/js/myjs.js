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
    $("#send-modal").on("show.bs.modal", function(event) {
        var route = $(event.relatedTarget).data('route')
        $(this).find('.modal-body form').attr('action', route)
    })
    $("#duplicate-modal").on("show.bs.modal", function(event) {
        var route = $(event.relatedTarget).data('route')
        $(this).find('.modal-body form').attr('action', route)
    })
    $("#blacklist-modal").on("show.bs.modal", function(event) {
        var route = $(event.relatedTarget).data('route')
        $(this).find('.modal-body form').attr('action', route)
    })
})

let myjs = function() {

    return {
        init: function() {
            console.log('myjs is running ..')
        }
    }
}()