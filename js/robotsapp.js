
// Handle JSON encoding of the data by registering a jQuery.ajax prefilter

$.ajaxPrefilter(function(options, orig, xhr ) {

    if ( options.processData
        && /^application\/json((\+|;).+)?$/i.test( options.contentType )
        && /^(post|put|delete)$/i.test( options.type )
        ) {
        options.data = JSON.stringify( orig.data );
    }
});
// Represent Data using Models
var Robot = can.Model({
    findAll: 'GET /api/robots',
    findOne: 'GET /api/robots/{id}',
    create:  {
        url: '/api/robots',
        type: 'POST',
        contentType: 'application/json'
    },
    update:  {
        url: '/api/robots/{id}',
        type: 'PUT',
        contentType: 'application/json'
    },
    destroy: {
        url: '/api/robots/{id}',
        type: 'DELETE',
        contentType: 'application/json'
    },
    search:  function(name){
        return $.ajax({
            url: '/api/robots/search/'+name,
            type: 'GET',
            dataType: 'json'})
    }
}, {});

//  Managing robots

var Robots = can.Control({
    init: function(el, options){
        var el = this.element;
        el.html(can.view('robotList', new Robot.List({})));
    },
    '.removebtn click': function(el, ev) {
        // ...destroy the corresponding to-do on the server.
        // The template will re-render itself and the
        // deleted to-do will be removed.
        var datadiv = el.parent().parent().parent().data('robot');
        datadiv.destroy();
    },
    '.editbtn click' : function(el, ev) {
        var datadiv = el.parent().parent().parent().data('robot');
        $('#id').val(datadiv.id);
        $('#name').val(datadiv.name);
        $('#type').val(datadiv.type);
        $('#year').val(datadiv.year);
        console.log(datadiv.id+' '+datadiv.name+' '+datadiv.type+' '+datadiv.year);
    }
});

// Bind click of add button to add more robots
$('.addbtn').bind('click', function(){
    // Get values of input fields
    var rName = $('#name').val();
    var rType = $('#type').val();
    var rYear = $('#year').val();
    var rId = $('#id').val();
    // Assign values to new robot
    var robot = new Robot({id:rId, name:rName, type: rType, year: rYear});
    //console.log(rName, rType, rYear);
    // Save robot and before updating robot list wait save action complete
    robot.save().then(function() {
        $('#robots').html(can.view('robotList', new Robot.List({})));
    }, function() {alert('Some Error');});
    $('#name').val('');
    $('#type').val('');
    $('#year').val('');
    $('#id').val('');
});

// Routing pulls the editor and the to-do board together
// and takes care of routing as well.
var Routing = can.Control({
    init: function() {
        // Declare what our routes will look like.
        can.route('api/robots/:id');
        // Fire up the to-do board.
        new Robots($('#robots'));
    }
});

// Kick the entire thing off by instantiating the
// Routing controller.
new Routing(document.body);