/* global window */
import { CHANGE, CLEAN } from './ElevationActionTypes';

export function changeKeyword(props) {
  return (dispatch) => {
    dispatch({
      type: CHANGE,
      payload: { ...props },
    });
  };
}

export function clean(props) {
  return (dispatch) => {
    dispatch({
      type: CLEAN,
      payload: { ...props },
    });
  };
}
