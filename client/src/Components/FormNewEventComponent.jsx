import React, { Component } from 'react';
import { BrowserRouter as Router } from 'react-router-dom';
import DefaultNewEventComponent from './DefaultNewEventComponent';
import SearchBarlocationComponent from './SearchBarLocationComponent';

class FormNewEventComponent extends Component {
    
    render() {
        return (
            <Router>
                <div className="container">
                    <h2 className="mt-5">Créer un évenements</h2>
                    <div className="row">
                        <div className="col-12 mx-auto">
                        <form>
                            <div className="form-group">
                                <input type="text" className="form-control" id="title-event" placeholder="Titre de l'évenements"/>
                            </div>
                            <div className="form-group">
                                <textarea className="form-control" id="desc-event" rows="4" placeholder="Description" />
                            </div>
                            <div className="form-group">
                                <input type="date" className="form-control"/>
                            </div>
                            <div className="form-group">
                                <SearchBarlocationComponent />
                            </div>
                            <button type="submit" className="btn-default">Créer</button>
                            </form>
                        </div>
                    </div>
                </div>
                <DefaultNewEventComponent />
            </Router>
        );
    }
}

export default FormNewEventComponent;