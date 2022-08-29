

var socket = io(script.dataset.ip + ':' + script.dataset.port, {
    transports: ['websocket'],
})

const id = document.getElementById('id').dataset.id

socket.on('refresh', data => {
    //  console.log(data +':'+ id )
    if (data == id) {
        location.reload();
    }
})



