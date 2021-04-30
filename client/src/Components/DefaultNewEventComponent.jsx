import React, { Component } from 'react';

class DefaultNewEventComponent extends Component {
    render() {
        return (
            <div className="container">
                <h2 className="mt-5">Modèles d'évenements</h2>
                <div className="row">
                    <div className="col-6 col-md-8">
                        <div className="card mt-3 text-center">
                            <div className="card-body p-1">
                                <h6 className="card-title">Anniversaires</h6>
                            </div>
                            <div className="card-img-top-small"></div>
                        </div>
                    </div>
                    <div className="col-6 col-md-8">
                        <div className="card mt-3 text-center">
                            <div className="card-body p-1">
                                <h6 className="card-title">Soirées</h6>
                            </div>
                            <div className="card-img-top-small"></div>
                        </div>
                    </div>
                    <div className="col-6 col-md-8">
                        <div className="card mt-3 text-center">
                            <div className="card-body p-1">
                                <h6 className="card-title">Vacances</h6>
                            </div>
                            <div className="card-img-top-small"></div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default DefaultNewEventComponent;