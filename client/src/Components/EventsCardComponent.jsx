import React, { Component } from 'react';
import { BrowserRouter as Router } from 'react-router-dom';
import SearchBarComponent from './SearchBarComponent.jsx';


class EventsCardComponent extends Component {
    render() {
        console.log(this.props);
        return (
            <Router>
                <div className="container mt-3">
                    <div className="row">
                        <div className="col">
                            <h3 className="text-dark">Bonjour, <span>Aurélien</span></h3>
                            <SearchBarComponent />
                        </div>
                    </div>
                    <h1 className="mt-5">Mes événements</h1>
                    <div className="row">
                        <div className="col-sm-12 col-md-6 col-lg-4">
                            <div className="card mt-3">
                                <div className="card-img-top"></div>
                                <div className="card-body">
                                    <h6 className="card-title">Titre de l'événements</h6>
                                    <p className="card-text"><i className="fas fa-calendar-alt"></i> Date</p>
                                    <p className="card-text"><i className="fas fa-map-marker-alt"></i> Lieu</p>
                                </div>
                            </div>
                        </div>
                        <div className="col-sm-12 col-md-6 col-lg-4">
                            <div className="card mt-3">
                                <div className="card-img-top"></div>
                                <div className="card-body">
                                    <h6 className="card-title">Titre de l'événements</h6>
                                    <p className="card-text"><i className="fas fa-calendar-alt"></i> Date</p>
                                    <p className="card-text"><i className="fas fa-map-marker-alt"></i> Lieu</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Router>
        );
    }
}

export default EventsCardComponent;