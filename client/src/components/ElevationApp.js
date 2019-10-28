import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import fieldHolder from 'components/FieldHolder/FieldHolder';
import * as ElevationActions from '../state/ElevationActions';
import Search from 'components/Search/Search';

class ElevationApp extends Component {
  constructor(props) {
    super(props);
    this.render = this.render.bind(this);
    this.changeKeyword = this.changeKeyword.bind(this);
    this.clean = this.clean.bind(this);
  }

  /**
   * Example props passed by <Search onSearch /> { InputName: 'Entered value' }
   * @param props
   */
  changeKeyword(props) {
    this.props.actions.changeKeyword(props);
  }

  /**
   * Passed the whole props and let reducer handle the changes
   */
  clean() {
    this.props.actions.clean(this.props);
  }

  render() {
    return (
      <div className="elevation-react-component">
        <input
          type="text"
          className={this.props.extraClass}
          value={this.props.value}
          id={this.props.id}
          name={this.props.name}
        />
        <button className="btn btn-lg font-icon-trash-bin" onClick={this.clean}>
          <span className="btn__title">[ {this.props.value} ]</span>
        </button>
        <Search
          {...this.props}
          onSearch={(props) => {this.changeKeyword(props)}}
          term={this.props.value}
          displayBehavior='TOGGLABLE'
        >
          searching...
        </Search>
      </div>
    );
  }
}

ElevationApp.propTypes = {
  name: PropTypes.string.isRequired,
  id: PropTypes.string.isRequired,
  actions: PropTypes.shape({
    Elevation: PropTypes.object,
  }),
};

function mapStateToProps(state, ownProps) {
  const { id, name } = ownProps;
  let value = ownProps.value;
  if (typeof state.FireSphere.Elevation[name] !== 'undefined') {
    value = state.FireSphere.Elevation[name];
  }
  return { id, value, name };
}

// Creates an actions to this.props eg. this.props.actions.SSCounter.function_name
function mapDispatchToProps(dispatch) {
  return { actions: bindActionCreators(ElevationActions, dispatch)};
}

const ConnectedElevationApp = connect(mapStateToProps, mapDispatchToProps)(ElevationApp);

export {
  ElevationApp as Component,
  ConnectedElevationApp,
};

export default fieldHolder(ConnectedElevationApp);
