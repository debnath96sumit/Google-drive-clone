function httpGet(url, params = {}){
    return fetch(url, {
        headers:{
            'Content-type' : 'application/json',
            'Accept' : 'application/json',
        }
    }).then(res => res.json());
}

export default httpGet;