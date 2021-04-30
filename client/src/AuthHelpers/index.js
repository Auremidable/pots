/**
 * @desc Authenticate the user
 * @param {*} user 
 */
export const Login = user => {
    return fetch('http://localhost:5000/login', {
        method: "POST",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json"
        },
        body: JSON.stringify(user)
    })
    .then( response => {
        return response.json();
    })
    .catch( error => console.log(error))
}


/**
 * @desc Store the token in local storage
 * @param {string} token 
 * @param {*} next 
 */
export const isUserLoggedIn = (token, next) => {
    if (typeof window !== "undefined") {
        localStorage.setItem("token", JSON.stringify(token));
        next();
    }
}

/**
 * @description Get the token from local storage
 */
export const isUserAuthenticated = () => {
    if (typeof window === "undefined"){
        return false;
    }
    let token = localStorage.getItem("token");
    if (token){        
        return token;
    }else{
        return false;
    }
}