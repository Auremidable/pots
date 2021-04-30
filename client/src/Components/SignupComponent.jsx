import React, { Component } from 'react';
import { BrowserRouter as Router, Link } from 'react-router-dom';
import axios from 'axios';

class SignupComponent extends Component {
    constructor(props) {
        super(props);
        this.state = {
            isRevealPassword: false,
            fields: {},
            errors: {},
            roles: {},
            errorMessage: null
        };
        this.togglePassword = this.togglePassword.bind(this);
    }

    handleValidation(){
        let fields = this.state.fields;
        let errors = {};
        let formIsValid = true;

        if(!fields["name"]){
            formIsValid = false;
            errors["name"] = "Veuillez remplir ce champ";
        }

        if(typeof fields["name"] !== "undefined"){
            if(fields["name"].length < 3 || fields["name"].length > 15){
                formIsValid = false;
                errors["name"] = "Doit être compris entre 3 et 15 caractères";
            }        
        }

        if(!fields["email"]){
            formIsValid = false;
            errors["email"] = "Veuillez remplir ce champ";
        }

        if(typeof fields["email"] !== "undefined"){
            let lastAtPos = fields["email"].lastIndexOf('@');
            let lastDotPos = fields["email"].lastIndexOf('.');

            if (!(lastAtPos < lastDotPos && lastAtPos > 0 && fields["email"].indexOf('@@') === -1 && lastDotPos > 2 && (fields["email"].length - lastDotPos) > 2)) {
                formIsValid = false;
                errors["email"] = "Email is not valid";
            }
        }

        if(!fields["password"]){
            formIsValid = false;
            errors["password"] = "Veuillez remplir ce champ";
        }
        
        this.setState({errors: errors});
        return formIsValid;
    }

    contactSubmit(e){
        e.preventDefault();

        const data = {
            username: this.state.fields["name"],
            email: this.state.fields["email"],
            password: this.state.fields["password"]
        };

        const config = {
            headers: {
            'Content-Type': 'application/json',
            }
        };

        axios.post('http://localhost:5000/register', data, config)
        .then(response => {
            window.location.replace('/');
        })
        .catch((error) => {
            this.setState({ errorMessage: error.response.data.message });
        })
    }

    isEmailAvailable(e) {
        e.preventDefault();
        e.persist();
        let errors = this.state.errors;

        if (!this.validateEmail(this.state.fields["email"])) {
            e.target.style.border = "1px solid red";
            errors.email = "Entrez une adresse mail valide";
            this.setState(errors);
            return false;
        }

        const config = {
            headers: {
            'Content-Type': 'application/json',
            }
        };

        axios.get('http://localhost:5000/is_email_available?email=' + this.state.fields["email"], config)
        .then(response => {
            if (response.data.message) {
                e.target.style.border = "1px solid green";
                errors.mail = "";
            }
            else {
                e.target.style.border = "1px solid red";
                errors.email = "Cette adresse mail est déjà utilisée.";
            }
            this.setState(errors);
        })
    }

    isUsernameAvailable(e) {
        e.preventDefault();
        e.persist();
        let errors = this.state.errors;

        const config = {
            headers: {
            'Content-Type': 'application/json',
            }
        };

        axios.get('http://localhost:5000/is_username_available?username=' + this.state.fields["name"], config)
        .then(response => {
            if (response.data.message){
                e.target.style.border = "1px solid green";
                errors.name = "";
            }
            else {
                e.target.style.border = "1px solid red";
                errors.name = "Ce nom d'utilisateur est déjà utilisé.";
            }
            this.setState(errors);
        })
    }

    handleChange(field, e){         
        let fields = this.state.fields;
        fields[field] = e.target.value;        
        this.setState({fields});
    }
    
    togglePassword() {
        this.setState({ isRevealPassword: !this.state.isRevealPassword });
    }

    validateEmail(email) {
        const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    render() {
        return (
            <Router>
                <div className="container h-100">
                    <div className="row h-100">
                        <div className="mx-auto col-md-8 col-lg-6 align-self-center">
                            <h1 className="text-center">Pot's</h1>
                            <h2 className="text-dark mt-5">Inscription</h2>
                            <p>Hey, rejoins-nous !</p>
                            {this.state.errorMessage ? <p className="text-center text-danger">{this.state.errorMessage}</p> : ""}
                            <form onSubmit={this.contactSubmit.bind(this)}>
                                <div className="form-group mt-4">
                                    <label htmlFor="name">name</label>
                                    <input 
                                    type="text" 
                                    className="form-control" 
                                    id="name" 
                                    onChange={this.handleChange.bind(this, "name")} value={this.state.fields.name || ''}        
                                    onBlur={this.isUsernameAvailable.bind(this)}
                                    />
                                    <span style={{color: "red"}}>{this.state.errors["name"]}</span>
                                </div>
                                <div className="form-group">
                                    <label htmlFor="email">Adresse mail</label>
                                    <input 
                                    type="email" 
                                    className="form-control" 
                                    id="email" 
                                    onChange={this.handleChange.bind(this, "email")} value={this.state.fields.email || ''}
                                    onBlur={this.isEmailAvailable.bind(this)}
                                    />
                                    <span style={{color: "red"}}>{this.state.errors["email"]}</span>
                                </div>
                                <div className="form-group">
                                    <label htmlFor="password">Mot de passe</label>
                                    <input
                                    name="myPassword"
                                    type={this.state.isRevealPassword ? "text" : "password"}
                                    className="form-control"
                                    id="password"
                                    onChange={this.handleChange.bind(this, "password")} value={this.state.fields.password || ''}
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
                                    <span style={{color: "red"}}>{this.state.errors["password"]}</span>
                                </div>
                                <div className="custom-control custom-checkbox">
                                    <input type="checkbox" className="custom-control-input" id="customCheck1"/>
                                    <label className="custom-control-label" htmlFor="customCheck1"><small className="text-muted">
                                        En cochant cette case, j'accepte de recevoir des informations venant de
                                    </small><span> Pot's</span></label>
                                </div>
                                <button type="submit" className="btn-default mt-3">
                                    S"inscrire
                                </button>
                            </form>
                            <p className="text-center mt-4">Vous avez un compte ?</p>
                            <p className="text-center mt-2">
                            <Link to="/signup">
                                Connexion
                            </Link>
                            </p>
                        </div>
                    </div>
                </div>
            </Router>
        );
    }
}

export default SignupComponent;