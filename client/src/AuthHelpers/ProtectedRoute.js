import React from 'react';
import { Route, Redirect } from "react-router-dom";
import { isUserAuthenticated } from './index';

/**
 * If token then we render the component (with all its props) else we force the redirection to the sign in page.
 * @param {*} param0 
 */
const ProtectedRoute = ({component: Component, ...rest}) => {
    return ( 
        <Route { ...rest } render={ props => isUserAuthenticated() 
            ? <Component { ...props }/> : <Redirect to='/'/>
        }/>
    );
}
 
export default ProtectedRoute;