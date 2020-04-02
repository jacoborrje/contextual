/**
 * Created by jacoborrje on 2019-05-10.
 */

/**
 * Created by jacoborrje on 2019-05-10.
 */

function confirmDelete(txt) {
    return confirm("Are you sure you want to delete this "+txt+"?");
}

function confirmEdit(txt) {
    return confirm("Are you sure you want to change this "+txt+"?");
}

function deactivateDropdownMenu(menuElement){
    menuElement.css("display", "none");
    dropdown = menuElement.children(":first");
    dropdown.val("0");
}

function activateDropdownMenu(menuElement, visibleAttr) {
    menuElement.css("display", visibleAttr);
}

function toggleDropdownActivation(elementID, menuElement, visibleAttr){
    if (elementID.checked)
    {
        activateDropdownMenu(menuElement, visibleAttr);
    } else {
        deactivateDropdownMenu(menuElement);
    }
}

function toggleVisible(elementID, hideClass){
    if (elementID.css("display")=="none"){
        if (hideClass !== undefined) {
            hideClass.each(function(){
                $(this).css("display", "none");
            })
        }
        elementID.css("display", "block");
    }
    else{
        elementID.css("display", "none");
    }
}

$.fn.extend({
    trackChanges: function() {
        $(":input",this).change(function() {
            $(this.form).data("changed", true);
        });
    }
    ,
    isChanged: function() {
        return this.data("changed");
    }
});

function confirmClose(formObject){
    if(formObject.isChanged()){
        return confirm("You have edited this source. Close it without saving?");
    }
}

function deleteNewMention(num){
    $('#mention'+num+'_preview').remove();
    $('#mentionCheckBoxes'+num).remove();
    $('#mention'+num).remove();
}

function deleteMention(num, mentionID, deleteLink){
    if (confirmDelete('mention')){
        $('#mention'+num+'_preview').attr({
            class:"deleted_mention"
        });
        $('#mentionCheckboxes'+num).attr({
            class:"deleted_mention"
        });
        var mentionElement = $('#mention'+num);
        mentionElement.attr({
            class:"deleted_mention",
            style:"display:none"
        });
        mentionElement.append($('<input>').attr({
            type: 'hidden',
            name: 'deleteMention['+num+']',
            id: 'deleteMention'+num,
            value: mentionID
        }));
        deleteLink.attr({
            onclick: 'undoMentionDelete('+num+', '+mentionID+', $(this));'
        });
        deleteLink.text('undo delete');
    }
}

function undoMentionDelete(num, mentionID, deleteLink){
    $('#deleteMention'+num).remove();
    deleteLink.attr({
        onclick:'return deleteMention('+num+', '+mentionID+', $(this));'
    })
    $('#mention'+num+'_preview').attr({
        class:"mention_preview"
    });
    $('#mentionCheckboxes'+num).attr({
        class:"mentionCheckbox"
    });
    $('#mention'+num).attr({
        class:"mention",
        style:"display:none"
    });
    deleteLink.text('delete');
}

function deleteTopic(topicID, deleteLink){
    if (confirmDelete('topic')){
        var topicSpan = $('#topic'+topicID);
        topicSpan.attr({
            class:"deleted_topic"
        });
        topicSpan.append($('<input>').attr({
            type: 'hidden',
            name: 'deleteTopic['+topicID+']',
            id: 'deleteTopic'+topicID,
            value: topicID
        }));
        deleteLink.attr({
            onclick: 'undoTopicDelete('+topicID+', $(this));'
        });
    }
}


function undoTopicDelete(topicID, deleteLink){
    $('#deleteTopic'+topicID).remove();
    deleteLink.attr({
        onclick:'return deleteTopic('+topicID+', $(this));'
    })
    $('#topic'+topicID).attr({
        class:"source_topic"
    });
    deleteLink.text('x');
}

function deleteAction(actionID, deleteLink){
    if (confirmDelete('action')){
        var actionList = $('#action'+actionID);
        actionList.attr({
            class:"deleted_action"
        });
        actionList.append($('<input>').attr({
            type: 'hidden',
            name: 'deleteAction['+actionID+']',
            id: 'deleteAction'+actionID,
            value: actionID
        }));
        deleteLink.attr({
            onclick: 'undoActionDelete('+actionID+', $(this));'
        });
        deleteLink.text('undo delete');
    }
}


function undoActionDelete(actionID, deleteLink){
    $('#deleteAction'+actionID).remove();
    deleteLink.attr({
        onclick:'return deleteAction('+actionID+', $(this));'
    })
    $('#action'+actionID).attr({
        class:"actionForm"
    });
    deleteLink.text('delete');
}

function deleteOccupation(num, occupationID, deleteLink){
    if (confirmDelete('occupation')){
        var actionList = $('#occupation'+num);
        actionList.attr({
            class:"deleted_occupation"
        });
        actionList.append($('<input>').attr({
            type: 'hidden',
            name: 'deleteOccupation['+occupationID+']',
            id: 'deleteOccupation'+occupationID,
            value: occupationID
        }));
        deleteLink.attr({
            onclick: 'undoOccupationDelete('+num+', '+occupationID+', $(this));'
        });
        deleteLink.text('undo delete');
    }
}

function undoOccupationDelete(num, occupationID, deleteLink){
    $('#deleteOccupation'+occupationID).remove();
    deleteLink.attr({
        onclick:'return deleteOccupation('+num+', ' + occupationID + ', $(this));'
    })
    $('#occupation'+num).attr({
        class:"occupationForm"
    });
    deleteLink.text('delete');
}


function deleteRelation(num, relationID, deleteLink){
    if (confirmDelete('relation')){
        var actionList = $('#relationship'+num);
        actionList.attr({
            class:"deleted_relation"
        });
        actionList.append($('<input>').attr({
            type: 'hidden',
            name: 'deleteRelation['+relationID+']',
            id: 'deleteRelation'+relationID,
            value: relationID
        }));
        deleteLink.attr({
            onclick: 'undoRelationDelete('+num+', '+relationID+', $(this));'
        });
        deleteLink.text('undo delete');
    }
}

function undoRelationDelete(num, relationID, deleteLink){
    $('#deleteRelation'+relationID).remove();
    deleteLink.attr({
        onclick:'return deleteRelation('+num+', ' + relationID + ', $(this));'
    })
    $('#relationship'+num).attr({
        class:"relationForm"
    });
    deleteLink.text('delete');
}

function deleteActorPlace(num, placeID, deleteLink){
    if (confirmDelete('actor place')){
        var actionList = $('#place'+num);
        actionList.attr({
            class:"deleted_place"
        });
        actionList.append($('<input>').attr({
            type: 'hidden',
            name: 'deleteActorPlace['+placeID+']',
            id: 'deleteActorPlace'+placeID,
            value: placeID
        }));
        deleteLink.attr({
            onclick: 'undoActorPlaceDelete('+num+', '+placeID+', $(this));'
        });
        deleteLink.text('undo delete');
    }
}

function undoActorPlaceDelete(num, placeID, deleteLink){
    $('#deletePlace'+placeID).remove();
    deleteLink.attr({
        onclick:'return deleteActorPlace('+num+', ' + placeID + ', $(this));'
    })
    $('#place'+num).attr({
        class:"placeForm"
    });
    deleteLink.text('delete');
}

function deactivateForm($element){
    $element.addClass('inactive');
    $element.find('input, select').each(function (e){
        $(this).addClass('inactive');
        $(this).prop('disabled', true);
    });
}

function activateForm($element){
    $element.removeClass('inactive');
    $element.find('input, select').each(function (){
        $(this).removeClass('inactive');
        $(this).prop('disabled', false);
    });
}

function toggleFormActivation($element, $select, $checkbox) {
    var valueSelected = $select.find("option:selected").val();
    if (!valueSelected && $checkbox.prop('checked')) {
        activateForm($element);
    }
    else {
        deactivateForm($element);
    }
}

function toggleCheckboxActivation($checkbox, $select) {
    var valueSelected = $select.find("option:selected").val();
    if (!valueSelected) {
        $checkbox.prop('disabled', false);
    }
    else {
        $checkbox.prop('disabled', true);
        $newPlaceCheckbox.prop('checked', false);

    }
}

function correspondentAutocomplete(inputField, correspondentIDField, correspondent_autocomplete_path){
    function log( message ) {
        $( "<div>" ).text( message ).prependTo( "#log" );
        $( "#log" ).scrollTop( 0 );
    }

    inputField.autocomplete({
        source: correspondent_autocomplete_path,
        minLength: 2,
        focus: function(event, ui){
            inputField.val( ui.item.name? ui.item.name : "" );
            correspondentIDField.val( ui.item.id? ui.item.id : "" );
            return false;
        },
        change: function( event, ui ) {
            inputField.val( ui.item? ui.item.name : "" );
            correspondentIDField.val( ui.item? ui.item.id : "" );
            return false;
        },
        select: function( event, ui ) {
            log( ui.item.name ?
                "Selected: " + ui.item.name + " aka " + ui.item.id :
                "Nothing selected, input was " + this.value );
            return false;
        },
    })
        .autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
            .append( "<div>" +item.name + " " + item.dates + " | " + item.type + "</div>" )
            .appendTo( ul );
    };
}

function actorAutocomplete(inputField, actorIDField, actor_autocomplete_path){
    function log( message ) {
        $( "<div>" ).text( message ).prependTo( "#log" );
        $( "#log" ).scrollTop( 0 );
    }
    inputField.autocomplete({
        source: actor_autocomplete_path,
        minLength: 2,
        focus: function(event, ui){
            return false;
        },
        change: function( event, ui ) {
            inputField.val( ui.item? ui.item.name : "" );
            actorIDField.val( ui.item? ui.item.id : "" );
            return false;
        },
        select: function( event, ui ) {
            log( ui.item.name ?
                "Selected: " + ui.item.name + " aka " + ui.item.id :
                "Nothing selected, input was " + this.value );
            inputField.val( ui.item.name? ui.item.name : "" );
            actorIDField.val( ui.item.id? ui.item.id : "" );
            return false;
        },
         })
        .autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
            .append( "<div>" +item.name + " " + item.dates + "</div>" )
            .appendTo( ul );
    };
}

function placeAutocomplete(inputField, placeIDField, place_autocomplete_path){
    function log( message ) {
        $( "<div>" ).text( message ).prependTo( "#log" );
        $( "#log" ).scrollTop( 0 );
    }

    inputField.autocomplete({
        source: place_autocomplete_path,
        minLength: 2,
        focus: function(event, ui){
            return false;
        },
        change: function( event, ui ) {
            inputField.val( ui.item? ui.item.name : "" );
            placeIDField.val( ui.item? ui.item.id : "" );
            return false;
        },
        select: function( event, ui ) {
            log( ui.item.name ?
                "Selected: " + ui.item.name + " aka " + ui.item.id :
                "Nothing selected, input was " + this.value );
            inputField.val( ui.item.name? ui.item.name : "" );
            placeIDField.val( ui.item.id? ui.item.id : "" );
            return false;
        },
    })
        .autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
            .append( "<div>" +item.name + ", " + item.parentName + " | " + item.type + "</div>" )
            .appendTo( ul );
    };
}

function institutionAutocomplete(inputField, institutionIDField, institution_autocomplete_path){
    function log( message ) {
        $( "<div>" ).text( message ).prependTo( "#log" );
        $( "#log" ).scrollTop( 0 );
    }

    inputField.autocomplete({
        source: institution_autocomplete_path,
        minLength: 2,
        focus: function(event, ui){
            inputField.val( ui.item.name? ui.item.name : "" );
            institutionIDField.val( ui.item.id? ui.item.id : "" );
            return false;
        },
        change: function( event, ui ) {
            inputField.val( ui.item? ui.item.name : "" );
            institutionIDField.val( ui.item? ui.item.id : "" );
            return false;
        },
        select: function( event, ui ) {
            log( ui.item.name ?
                "Selected: " + ui.item.name + " aka " + ui.item.id :
                "Nothing selected, input was " + this.value );


            return false;
        },
    })
        .autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
            .append( "<div>" +item.name + "</div>" )
            .appendTo( ul );
    };
}

function occupationAutocomplete(inputField, occupationIDField, occupation_autocomplete_path){
    function log( message ) {
        $( "<div>" ).text( message ).prependTo( "#log" );
        $( "#log" ).scrollTop( 0 );
    }

    inputField.autocomplete({
        source: occupation_autocomplete_path,
        minLength: 2,
        focus: function(event, ui){
            inputField.val( ui.item.name? ui.item.name : "" );
            occupationIDField.val( ui.item.id? ui.item.id : "" );
            return false;
        },
        change: function( event, ui ) {
            inputField.val( ui.item? ui.item.name : "" );
            occupationIDField.val( ui.item? ui.item.id : "" );
            return false;
        },
        select: function( event, ui ) {
            log( ui.item.name ?
                "Selected: " + ui.item.name + " aka " + ui.item.id :
                "Nothing selected, input was " + this.value );
            return false;
        },
    })
        .autocomplete( "instance" )._renderItem = function( ul, item ) {
        return $( "<li>" )
            .append( "<div>" +item.name + "</div>" )
            .appendTo( ul );
    };
}