import React, { Component } from 'react';

class SearchBarComponent extends Component {
    render() {
        return (
            <div className="search-bar-container mt-3">
                <input type="search" placeholder="Rechercher..." className="search-bar"/>
            </div>
        );
    }
}

export default SearchBarComponent;