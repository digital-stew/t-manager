const socket = io('http://10.0.0.55:3000', { transports : ['websocket'] })
const id = document.getElementById('id').dataset.id


socket.on('refresh', data => {
    //  console.log(data +':'+ id )
    if (data == id ) {
        location.reload(); 
    }
})



