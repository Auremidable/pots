import React, { Component } from 'react';

class ModuleComponent extends Component {
    constructor(props) {
        super(props);
        this.state = {
            loaded: false,
            error: false,
            content: null
        }
        this.handleClick = this.handleClick.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    perform(callback, action, data = false, method = 'GET') {
        let request = {
            method: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': localStorage.getItem('token')
            }
        };
        if (data)
            request.body = data
        return fetch('http://localhost:5000' + action, request)
        .then(response => {
            if (response.status != 200)
                throw new Error("Erreur lors du chargement du module.");
            return response.text();
        })
        .then(content => callback(content))
        .catch( error => this.setState({error: error.message}))
    }
    
    componentDidMount() {
        let that = this;
        this.perform(function (content) {
            that.setState({content: content, loaded: true})
        }, '/api/eventModules/' + this.props.id + '/mainPage')
    }

    componentDidUpdate() {
        let clicks = document.querySelectorAll('.module_click');
        clicks.forEach(click => {
            click.addEventListener('click', this.handleClick);
        });
        let submits = document.querySelectorAll('.module_submit');
        submits.forEach(submit => {
            submit.addEventListener('submit', this.handleSubmit);
        });
    }

    handleClick(e) {
        e.preventDefault();
        let that = this;
        let target = e.target.closest('.module_click')
        let action = target.dataset.action;
        let params = JSON.stringify({'more': target.dataset.more});
        this.perform(function (content) {
            that.setState({content: content})
        }, '/api/eventModules/' + this.props.id + '/' + action, params, 'POST');
    }

    handleSubmit(e) {
        e.preventDefault();
        let that = this;
        let action = e.target.getAttribute('action');
        let formdata = new FormData(e.target);
        let tpm = {};
        formdata.forEach((value, key) => tpm[key] = value);
        var params = JSON.stringify(tpm);
        this.perform(function (content) {
            that.setState({content: content})
        }, '/api/eventModules/' + this.props.id + '/' + action, params, 'POST');
    }

    render() {
        const {loaded, error, content} = this.state;
        if (error)
            return <h1>{error}</h1>
        if (!loaded)
            return <h1>Chargement...</h1>
        return (
            <div className="container h-100">
                <div dangerouslySetInnerHTML={{__html: content}} className="row h-100"/>
            </div>
        )
    }
}

export default ModuleComponent;