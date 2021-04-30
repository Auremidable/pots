import React, { Component } from "react";
import { Link, Redirect } from 'react-router-dom';
import { Login, isUserLoggedIn } from '../AuthHelpers';

class LoginComponent extends Component {
    constructor(props) {
        super(props);
        this.state = {
            email: "",
            password: "",            
            errorMessage: "",
            redirection: false,
            isRevealPassword: false,
        };
        this.togglePassword = this.togglePassword.bind(this);
    }

    togglePassword() {
        this.setState({ isRevealPassword: !this.state.isRevealPassword });
    }
    
    /**
     * @param {*} name 
     */
    onInputChange = name => e => {
        this.setState({ [name]: e.target.value }) 
        this.setState({ errorMessage: "" });
    }

    /**
     * @param {*} e 
     */
    handleFormSubmit = e => {
        e.preventDefault();
        const { email, password } = this.state;
        const user = { email, password }        
        Login(user)
            .then( response => {
                if (response.code === 200) {    
                    console.log(response);                
                    isUserLoggedIn( response.token, () => {
                        localStorage.setItem("token", response.token);
                        localStorage.setItem("user", JSON.stringify(response.user));
                        this.setState({ redirection: true })
                    });
                }else{
                    this.setState({ errorMessage: response.message });                                   
                }
            })
            .catch( error => console.log(error));
    }


    render() {  
        const { email, password, redirection } = this.state;
        if (redirection) {
            return <Redirect to="/home" />
        }
        return (            
            <div className="container h-100">
                    <div className="row h-100">
                        <div className="mx-auto col-md-8 col-lg-6 align-self-center">
                        <h1 className="text-center">Pot's</h1>
                        {this.state.errorMessage ? <p className="text-center text-danger">{this.state.errorMessage}</p> : ""}
                        <h2 className="text-dark mt-5">Connexion</h2>
                        <p>Hey, ravi de vous revoir !</p>
                        <form onSubmit={ this.handleFormSubmit }>
                            <div className="form-group mt-5">
                            <label htmlFor="email">Adresse mail</label>
                            <input 
                                onChange={ this.onInputChange('email') }
                                value={ email }
                                name="email"
                                type="email" 
                                className="form-control" 
                                id="email" 
                            />
                            <div className="invalid-feedback" id="email-checked">
                                Adresse email incorrect.
                            </div>
                            </div>
                            <div className="form-group">
                            <label htmlFor="password">Mot de passe</label>
                            <input
                                onChange={ this.onInputChange('password') }
                                value={ password }
                                name="password"
                                type={this.state.isRevealPassword ? "text" : "password"}
                                className="form-control"
                                id="password"
                            />
                            <span
                                onClick={this.togglePassword}
                                id="toggle-password"
                                className="field-icon"
                            >
                                {this.state.isRevealPassword ? (
                                <i className="fas fa-eye"></i>
                                ) : (
                                <i className="fas fa-eye-slash"></i>
                                )}
                            </span>
                            <div className="invalid-feedback" id="password-checked">
                                Mot de passe incorrect.
                            </div>
                            </div>
                            <div className="custom-control custom-checkbox">
                                <input 
                                    type="checkbox" 
                                    className="custom-control-input" 
                                    id="customCheck1"
                                />
                                <label className="custom-control-label" htmlFor="customCheck1"><small className="text-muted">
                                    Se souvenir de moi
                                </small></label>
                            </div>
                            <button type="submit" className="btn-default mt-5">
                            Se connecter
                            </button>
                        </form>
                        <p className="text-center mt-4">Pas encore de compte ?</p>
                        <p className="text-center mt-2">
                            <Link to="/signup">
                            Inscrivez-vous
                            </Link>
                        </p>
                        </div>
                    </div>
                </div>            
        );
    }
}

export default LoginComponent;
